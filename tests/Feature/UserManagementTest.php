<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createSuperAdmin(): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'Super Admin']);

        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        $superAdmin->assignRole('Super Admin');

        return $superAdmin;
    }

    public function test_super_admin_can_open_the_user_edit_panel(): void
    {
        $department = Department::create(['name' => 'ICT Department']);
        $unit = $department->units()->create(['name' => 'Software Development Unit']);
        $superAdmin = $this->createSuperAdmin();

        $pendingUser = User::factory()->create([
            'name' => 'Norah Test',
            'first_name' => 'Norah',
            'second_name' => 'Test',
            'email' => 'norah@example.com',
            'phone_number' => '+256700000001',
            'department_id' => $department->id,
            'unit_id' => $unit->id,
            'requested_role' => 'Staff',
            'approval_status' => 'pending',
            'is_active' => false,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('users.management.index', [
            'edit_user' => $pendingUser->id,
        ]));

        $response->assertOk();
        $response->assertSee('Edit User');
        $response->assertSee('Norah Test');
        $response->assertSee('Save User Changes');
        $response->assertSee('Back to current list');
        $response->assertSee('All users');
    }

    public function test_approved_users_cannot_be_rejected_from_management(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $approvedUser = User::factory()->create([
            'name' => 'Approved User',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('users.management.index', [
            'status' => 'approved',
        ]));

        $response->assertOk();
        $response->assertSee('Approved User');
        $response->assertDontSee('btn btn-sm btn-outline-danger', false);

        $rejectResponse = $this->actingAs($superAdmin)->post(route('users.management.reject', $approvedUser));

        $rejectResponse->assertStatus(422);
        $this->assertSame('approved', $approvedUser->fresh()->approval_status);
    }

    public function test_role_management_page_shows_and_updates_role_permissions(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $staffRole = Role::firstOrCreate(['name' => 'Staff']);

        Permission::firstOrCreate(['name' => 'submit daily reports']);
        Permission::firstOrCreate(['name' => 'view reports']);
        Permission::firstOrCreate(['name' => 'manage goals']);

        $staffRole->syncPermissions(['submit daily reports']);

        $usersResponse = $this->actingAs($superAdmin)->get(route('users.management.index'));

        $usersResponse->assertOk();
        $usersResponse->assertDontSee('Role Permissions');

        $response = $this->actingAs($superAdmin)->get(route('roles.management.index'));

        $response->assertOk();
        $response->assertSee('Role Management');
        $response->assertSee('Submit Reports');
        $response->assertSee('Manage Goals');

        $this->actingAs($superAdmin)->put(route('roles.management.permissions.update', $staffRole), [
            'permissions' => ['submit daily reports', 'view reports'],
        ])->assertRedirect();

        $this->assertTrue($staffRole->fresh()->hasPermissionTo('submit daily reports'));
        $this->assertTrue($staffRole->fresh()->hasPermissionTo('view reports'));
        $this->assertFalse($staffRole->fresh()->hasPermissionTo('manage goals'));
    }

    public function test_super_admin_can_create_role_with_permissions(): void
    {
        $superAdmin = $this->createSuperAdmin();

        Permission::firstOrCreate(['name' => 'manage goals']);
        Permission::firstOrCreate(['name' => 'view reports']);

        $this->actingAs($superAdmin)->post(route('roles.management.store'), [
            'name' => 'program manager',
            'permissions' => ['manage goals', 'view reports'],
        ])->assertRedirect();

        $role = Role::where('name', 'Program Manager')->first();

        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('manage goals'));
        $this->assertTrue($role->hasPermissionTo('view reports'));
    }

    public function test_super_admin_can_assign_custom_role_to_user(): void
    {
        $department = Department::create(['name' => 'Programs Department']);
        $superAdmin = $this->createSuperAdmin();
        Role::firstOrCreate(['name' => 'Program Manager']);

        $user = User::factory()->create([
            'first_name' => 'Grace',
            'second_name' => 'Planner',
            'name' => 'Grace Planner',
            'email' => 'grace@example.com',
            'phone_number' => '+256700000099',
            'department_id' => $department->id,
            'requested_role' => 'Staff',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $this->actingAs($superAdmin)->put(route('users.management.update', $user), [
            'first_name' => 'Grace',
            'second_name' => 'Planner',
            'email' => 'grace@example.com',
            'phone_number' => '+256700000099',
            'department_id' => $department->id,
            'unit_id' => null,
            'requested_role' => 'Program Manager',
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertSame('Program Manager', $user->fresh()->requested_role);
        $this->assertTrue($user->fresh()->hasRole('Program Manager'));
    }

    public function test_admin_cannot_create_roles(): void
    {
        Role::firstOrCreate(['name' => 'Admin']);
        Permission::firstOrCreate(['name' => 'manage goals']);

        $admin = User::factory()->create([
            'role' => 'admin',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        $admin->assignRole('Admin');

        $this->actingAs($admin)->post(route('roles.management.store'), [
            'name' => 'Program Manager',
            'permissions' => ['manage goals'],
        ])->assertForbidden();

        $this->assertDatabaseMissing('roles', ['name' => 'Program Manager']);
    }
}
