<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $departments = [
            'ICT Department' => ['Software Development Unit', 'Infrastructure Unit', 'Helpdesk Unit'],
            'Finance Department' => ['Accounts Unit', 'Procurement Unit'],
            'Programs Department' => ['Child Sponsorship Unit', 'Community Development Unit'],
            'Human Resources Department' => ['People Operations Unit', 'Training Unit'],
            'Administration Department' => ['Facilities Unit', 'Transport Unit'],
        ];

        foreach ($departments as $departmentName => $units) {
            $department = Department::firstOrCreate(['name' => $departmentName], [
                'code' => strtoupper(substr(str_replace(' Department', '', $departmentName), 0, 3)),
            ]);

            foreach ($units as $unitName) {
                $department->units()->firstOrCreate(['name' => $unitName]);
            }
        }

        $permissions = [
            'manage departments',
            'manage units',
            'manage users',
            'manage quarters',
            'manage goals',
            'review goals',
            'submit weekly updates',
            'view reports',
            'export reports',
            'view organization dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $superAdminRole = Role::findOrCreate('Super Admin');
        $adminRole = Role::findOrCreate('Admin');
        $managerRole = Role::findOrCreate('Manager');
        $supervisorRole = Role::findOrCreate('Supervisor');
        $staffRole = Role::findOrCreate('Staff');

        $superAdminRole->syncPermissions($permissions);
        $adminRole->syncPermissions($permissions);
        $managerRole->syncPermissions([
            'manage goals',
            'review goals',
            'view reports',
            'export reports',
        ]);
        $supervisorRole->syncPermissions([
            'review goals',
            'view reports',
        ]);
        $staffRole->syncPermissions([
            'submit weekly updates',
            'view reports',
        ]);

        $superAdmin = User::updateOrCreate([
            'email' => 'superadmin@arm.test',
        ], [
            'name' => 'Super Admin',
            'first_name' => 'Super',
            'second_name' => 'Admin',
            'password' => Hash::make('Sysadmin2@@2'),
            'role' => 'admin',
            'requested_role' => 'Super Admin',
            'approval_status' => 'approved',
            'is_active' => true,
            'approved_at' => now(),
            'email_verified_at' => now(),
        ]);

        $superAdmin->assignRole($superAdminRole);
    }
}
