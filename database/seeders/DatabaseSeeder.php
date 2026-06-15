<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\CompanySetting;
use App\Models\Quarter;
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
            'ICT Department' => [
                'description' => 'Technology support, systems, infrastructure, and digital service delivery.',
                'units' => ['Software Development Unit', 'Infrastructure Unit', 'Helpdesk Unit'],
            ],
            'Finance Department' => [
                'description' => 'Financial planning, accounting, procurement, and stewardship controls.',
                'units' => ['Accounts Unit', 'Procurement Unit'],
            ],
            'Programs Department' => [
                'description' => 'Program delivery, sponsorship, community development, and field operations.',
                'units' => ['Child Sponsorship Unit', 'Community Development Unit'],
            ],
            'Human Resources Department' => [
                'description' => 'People operations, performance, staff support, and learning coordination.',
                'units' => ['People Operations Unit', 'Training Unit'],
            ],
            'Administration Department' => [
                'description' => 'Facilities, transport, office coordination, and operational administration.',
                'units' => ['Facilities Unit', 'Transport Unit'],
            ],
        ];

        CompanySetting::firstOrCreate([], [
            'company_name' => 'Africa Renewal Ministries',
            'company_short_name' => 'Africa Renewal',
            'brand_mark' => '90',
            'product_name' => 'SMART Goals Tracker',
            'tagline' => 'Plan, review, approve, and report on measurable goals.',
        ]);

        $departmentIndex = 1;

        foreach ($departments as $departmentName => $details) {
            $department = Department::updateOrCreate(['name' => $departmentName], [
                'code' => $this->departmentCode($departmentIndex),
                'description' => $details['description'],
            ]);

            foreach ($details['units'] as $unitIndex => $unitName) {
                $department->units()->updateOrCreate(['name' => $unitName], [
                    'code' => $this->unitCode($departmentIndex, $unitIndex + 1),
                    'description' => "Operational unit under {$departmentName}.",
                ]);
            }

            $departmentIndex++;
        }

        $quarters = [
            ['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31'],
            ['name' => 'Q2 2026', 'starts_at' => '2026-04-01', 'ends_at' => '2026-06-30'],
            ['name' => 'Q3 2026', 'starts_at' => '2026-07-01', 'ends_at' => '2026-09-30'],
            ['name' => 'Q4 2026', 'starts_at' => '2026-10-01', 'ends_at' => '2026-12-31'],
        ];

        foreach ($quarters as $quarter) {
            Quarter::updateOrCreate(['name' => $quarter['name']], $quarter + [
                'is_active' => now()->betweenIncluded($quarter['starts_at'], $quarter['ends_at']),
            ]);
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

    private function departmentCode(int $index): string
    {
        return (string) (100000 + $index);
    }

    private function unitCode(int $departmentIndex, int $unitIndex): string
    {
        return 'U'.str_pad((string) $departmentIndex, 2, '0', STR_PAD_LEFT).str_pad((string) $unitIndex, 2, '0', STR_PAD_LEFT);
    }
}
