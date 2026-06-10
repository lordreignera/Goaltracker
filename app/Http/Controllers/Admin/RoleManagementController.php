<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleManagementController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        return view('roles.management', [
            'permissionRoles' => Role::with('permissions')
                ->get()
                ->sortBy(fn (Role $role) => array_search($role->name, ['Super Admin', 'Admin', 'Manager', 'Supervisor', 'Staff'], true)),
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);
        $request->merge(['name' => Str::headline($request->input('name'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $role = Role::create(['name' => Str::headline($data['name'])]);
        $role->syncPermissions($data['permissions'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('status', "{$role->name} role has been created.");
    }

    public function updatePermissions(Request $request, Role $role)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);
        abort_if($role->name === 'Super Admin', 422, 'Super Admin permissions cannot be changed.');

        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $role->syncPermissions($data['permissions'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('status', "{$role->name} permissions have been updated.");
    }
}
