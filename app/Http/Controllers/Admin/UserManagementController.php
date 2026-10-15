<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Department;
use App\Models\Position;
use App\Models\Section;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin() || $request->user()->can('manage users'), 403);

        $status = $request->input('status', 'pending');

        $users = User::with(['department', 'accessibleDepartments', 'section', 'position', 'roles'])
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
            $editUser = User::with(['department', 'accessibleDepartments', 'section', 'position', 'roles'])->find($request->edit_user);

            if ($editUser?->isSuperAdmin()) {
                $editUser = null;
            }
        }

        return view('users.management', [
            'users' => $users,
            'editUser' => $editUser,
            'departments' => Department::with('sections.positions')->orderBy('name')->get(),
            'sections' => Section::with('department')->orderBy('name')->get(),
            'positions' => Position::with(['department', 'section'])->orderBy('title')->get(),
            'roles' => Role::where('name', '!=', 'Super Admin')->orderBy('name')->pluck('name'),
            'status' => $status,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user, AuditLogger $audit)
    {
        $data = $request->validated();
        $oldAccess = $this->accessSnapshot($user);

        $user->forceFill([
            'first_name' => $data['first_name'],
            'second_name' => $data['second_name'],
            'name' => trim($data['first_name'].' '.$data['second_name']),
            'phone_number' => $data['phone_number'],
            'email' => $data['email'],
            'department_id' => $data['department_id'],
            'section_id' => $data['section_id'] ?? null,
            'position_id' => $data['position_id'] ?? null,
            'requested_role' => $data['requested_role'],
            'is_active' => $request->boolean('is_active'),
        ])->save();

        $this->syncDepartmentAccess($user, $data['department_ids'] ?? []);

        if ($user->approval_status === 'approved') {
            $this->assignApprovedRole($user);
        }

        $audit->record(
            category: 'security',
            action: 'user.access_updated',
            description: "Access settings for {$user->name} were updated.",
            subject: $user,
            oldValues: $oldAccess,
            newValues: $this->accessSnapshot($user->refresh()),
        );

        return back()->with('status', "{$user->name} has been updated.");
    }

    public function approve(Request $request, User $user, AuditLogger $audit)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);
        abort_if($user->isSuperAdmin(), 422, 'Super Admin accounts are protected.');
        abort_if($user->approval_status === 'approved', 422, 'This account is already approved.');

        $user->forceFill([
            'approval_status' => 'approved',
            'is_active' => true,
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ])->save();

        $this->assignApprovedRole($user);
        $this->syncDepartmentAccess($user, $user->accessibleDepartmentIds());

        $audit->record(
            category: 'security',
            action: 'user.approved',
            description: "{$user->name} was approved and granted system access.",
            subject: $user,
            newValues: $this->accessSnapshot($user->refresh()),
        );

        return back()->with('status', "{$user->name} has been approved.");
    }

    public function reject(Request $request, User $user, AuditLogger $audit)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);
        abort_if($user->approval_status === 'approved', 422, 'Approved accounts cannot be rejected. Deactivate the account if access should be removed.');

        $user->forceFill([
            'approval_status' => 'rejected',
            'is_active' => false,
            'approved_at' => null,
            'approved_by' => $request->user()->id,
        ])->save();

        $user->syncRoles([]);

        $audit->record(
            category: 'security',
            action: 'user.rejected',
            description: "{$user->name}'s account request was rejected.",
            subject: $user,
            newValues: $this->accessSnapshot($user->refresh()),
        );

        return back()->with('status', "{$user->name} has been rejected.");
    }

    public function destroy(Request $request, User $user, AuditLogger $audit)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);
        abort_if($request->user()->is($user), 422, 'You cannot delete your own account.');
        abort_if($user->isSuperAdmin(), 422, 'Super Admin accounts are protected and cannot be deleted here.');

        $hasAuditHistory = $user->approvedUsers()->exists()
            || $user->createdGoals()->exists()
            || $user->ownedGoals()->exists()
            || $user->approvedGoals()->exists()
            || $user->weeklyUpdates()->exists()
            || $user->supervisorReviews()->exists()
            || $user->quarterlyReflections()->exists();

        if ($hasAuditHistory) {
            return back()->withErrors([
                'user' => 'This user has approval, goal, report, review, or reflection history and cannot be deleted. Deactivate the account instead.',
            ]);
        }

        $name = $user->name;
        $audit->record(
            category: 'security',
            action: 'user.deletion_authorized',
            description: "Deletion of {$name}'s account was authorized.",
            subject: $user,
            oldValues: $this->accessSnapshot($user),
        );
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

    private function syncDepartmentAccess(User $user, array $departmentIds): void
    {
        $ids = collect($departmentIds)
            ->push($user->department_id)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $user->accessibleDepartments()->sync($ids);
    }

    private function accessSnapshot(User $user): array
    {
        return [
            'approval_status' => $user->approval_status,
            'is_active' => $user->is_active,
            'requested_role' => $user->requested_role,
            'roles' => $user->roles()->pluck('name')->sort()->values()->all(),
            'department_ids' => $user->accessibleDepartments()->pluck('departments.id')->map(fn ($id) => (int) $id)->sort()->values()->all(),
            'section_id' => $user->section_id,
            'position_id' => $user->position_id,
        ];
    }
}
