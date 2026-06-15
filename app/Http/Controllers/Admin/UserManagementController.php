<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Department;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin() || $request->user()->can('manage users'), 403);

        $status = $request->input('status', 'pending');

        $users = User::with(['department', 'unit', 'roles'])
            ->when($status !== 'all', fn ($query) => $query->where('approval_status', $status))
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%'.$request->search.'%')
                        ->orWhere('email', 'like', '%'.$request->search.'%')
                        ->orWhere('phone_number', 'like', '%'.$request->search.'%');
                });
            })
            ->when($request->filled('department_id'), fn ($query) => $query->where('department_id', $request->department_id))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $editUser = null;
        if ($request->filled('edit_user')) {
            $editUser = User::with(['department', 'unit', 'roles'])->find($request->edit_user);
        }

        return view('users.management', [
            'users' => $users,
            'editUser' => $editUser,
            'departments' => Department::with('units')->orderBy('name')->get(),
            'units' => Unit::with('department')->orderBy('name')->get(),
            'roles' => Role::where('name', '!=', 'Super Admin')->orderBy('name')->pluck('name'),
            'status' => $status,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        $user->forceFill([
            'first_name' => $data['first_name'],
            'second_name' => $data['second_name'],
            'name' => trim($data['first_name'].' '.$data['second_name']),
            'phone_number' => $data['phone_number'],
            'email' => $data['email'],
            'department_id' => $data['department_id'],
            'unit_id' => $data['unit_id'] ?? null,
            'requested_role' => $data['requested_role'],
            'is_active' => $request->boolean('is_active'),
        ])->save();

        if ($user->approval_status === 'approved') {
            $this->assignApprovedRole($user);
        }

        return back()->with('status', "{$user->name} has been updated.");
    }

    public function approve(Request $request, User $user)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $user->forceFill([
            'approval_status' => 'approved',
            'is_active' => true,
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ])->save();

        $this->assignApprovedRole($user);

        return back()->with('status', "{$user->name} has been approved.");
    }

    public function reject(Request $request, User $user)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);
        abort_if($user->approval_status === 'approved', 422, 'Approved accounts cannot be rejected. Delete the account if it should be removed.');

        $user->forceFill([
            'approval_status' => 'rejected',
            'is_active' => false,
            'approved_at' => null,
            'approved_by' => $request->user()->id,
        ])->save();

        $user->syncRoles([]);

        return back()->with('status', "{$user->name} has been rejected.");
    }

    public function destroy(Request $request, User $user)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);
        abort_if($request->user()->is($user), 422, 'You cannot delete your own account.');

        $name = $user->name;
        $user->delete();

        return back()->with('status', "{$name} has been deleted.");
    }

    private function assignApprovedRole(User $user): void
    {
        $role = $user->requested_role ?: 'Staff';

        $user->forceFill([
            'role' => Str::of($role)->lower()->replace(' ', '_')->toString(),
        ])->save();

        $user->syncRoles([$role]);
    }
}
