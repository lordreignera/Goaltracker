<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\CompanySetting;
use App\Models\GoalPillar;
use App\Models\Quarter;
use App\Models\User;
use App\Models\Position;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
            'Secretariat' => [
                'description' => 'Executive, administration, people, communications, partnerships, audit, and fundraising functions.',
                'sections' => [
                    'Executive Office' => [
                        'CEO Assistant',
                        'Executive Director',
                        'ED Assistant',
                    ],
                    'Human Resource' => [
                        'HR Manager',
                        'Front Desk',
                    ],
                    'Communications and PR' => [
                        'Communications & PR Manager',
                        'Communications Coordinator',
                    ],
                    'Audit' => [
                        'Auditor Officer',
                        'Auditor Assistant',
                    ],
                    'Procurement and Fleet' => [
                        'Fleet & Procurement Officer',
                        'Logistics Officer',
                        'Estates Officer',
                        'Driver',
                    ],
                    'Partnerships' => [
                        'Partnership Facilitator',
                        'Partnerships Facilitator Assistant',
                    ],
                    'Support Staff' => [
                        'Support Staff - Tea',
                        'Support Staff - Cleaning',
                    ],
                ],
            ],
            'SEC-Finance' => [
                'description' => 'Financial planning, accounting, procurement, and stewardship controls.',
                'sections' => [
                    'Finance Management' => [
                        'Finance Manager',
                        'Chief Accountant',
                    ],
                    'Accounting' => [
                        'Senior Accountant',
                        'Accountant 1',
                        'Accountant 2',
                        'Accountant 3',
                        'Accounts Assistant',
                    ],
                ],
            ],
            'Childcare' => [
                'description' => 'Child sponsorship, care, protection, and related program support.',
                'sections' => [
                    'Child Development Programs' => [
                        'Child Development Director',
                        'Employee Relations Manager - CDPS',
                        'CSR Coordinator',
                    ],
                    'Child Sponsorship Relations' => [
                        'CSR Officers',
                        'CSR Support Staff',
                    ],
                    'Local Sponsorship' => [
                        'Local Sponsorship',
                    ],
                ],
            ],
            'Community Outreach' => [
                'description' => 'Community transformation, outreach, and field operations.',
                'sections' => [
                    'Community Outreach Office' => [
                        'Community Outreach Director',
                        'CDD Assistant',
                    ],
                    'Community Programs' => [
                        'Program Officer 1',
                        'Program Officer 2',
                        'Program Officer 3',
                    ],
                    'Adjumani' => [
                        'Adjumani Coordinator',
                        'Adjumani Officer',
                    ],
                    'Mwangaza' => [
                        'Mwangaza Coordinator',
                    ],
                    'SACCO' => [
                        'SACCO Coordinator',
                    ],
                ],
            ],
            'Equip' => [
                'description' => 'Training, equipping, and capacity development.',
                'sections' => [
                    'Equip Office' => [
                        'Equip Director',
                    ],
                    'Africa Renewal University' => [
                        'AFRU',
                    ],
                    'Training and Leadership' => [
                        'TLT Coordinator',
                        'NGLP Coordinator',
                    ],
                    'Mobile Bible School' => [
                        'Mobile Bible School',
                    ],
                ],
            ],
            'Healthy Church & Missions' => [
                'description' => 'Church health, ministry partnerships, and missions work.',
                'sections' => [
                    'Healthy Church Office' => [
                        'Chief Ministry Officer - Programs',
                    ],
                    'Renewal Health Network' => [
                        'Renewal Health Network',
                    ],
                    'Bethany' => [
                        'Bethany Manager',
                    ],
                    'LHBH' => [
                        'LHBH Manager',
                    ],
                    'Mercy Network' => [
                        'Mercy Network',
                    ],
                    'Missions and Partnerships' => [
                        'Missions & Partnerships',
                        'Ministry Advocate',
                    ],
                    'Church Planting' => [
                        'Church Planting',
                    ],
                ],
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

            foreach ($details['sections'] as $sectionIndex => $sectionUnits) {
                $sectionName = is_string($sectionIndex) ? $sectionIndex : $sectionUnits;
                $units = is_array($sectionUnits) ? $sectionUnits : [];

                $section = $department->sections()->updateOrCreate(['name' => $sectionName], [
                    'code' => $this->sectionCode($departmentIndex, $sectionIndex),
                    'description' => "Operational section under {$departmentName}.",
                ]);

                foreach ($units as $unitIndex => $unitName) {
                    $unit = $section->units()->updateOrCreate(['name' => $unitName], [
                        'department_id' => $department->id,
                        'code' => $this->unitCode($departmentIndex, $unitIndex + 1),
                        'description' => "Operational unit under {$sectionName}.",
                    ]);

                    Position::updateOrCreate([
                        'department_id' => $department->id,
                        'section_id' => $section->id,
                        'title' => $unitName,
                    ], [
                        'unit_id' => $unit->id,
                        'code' => $this->positionCode($departmentIndex, $unitIndex + 1),
                        'description' => "Organogram position under {$sectionName}.",
                    ]);
                }
            }

            $departmentIndex++;
        }

        $quarters = [
            ['name' => 'Q1 2026/2027', 'starts_at' => '2026-07-01', 'ends_at' => '2026-09-30'],
            ['name' => 'Q2 2026/2027', 'starts_at' => '2026-10-01', 'ends_at' => '2026-12-31'],
            ['name' => 'Q3 2026/2027', 'starts_at' => '2027-01-01', 'ends_at' => '2027-03-31'],
            ['name' => 'Q4 2026/2027', 'starts_at' => '2027-04-01', 'ends_at' => '2027-06-30'],
        ];

        foreach ($quarters as $quarter) {
            $existingQuarter = Quarter::where('name', $quarter['name'])->first()
                ?? Quarter::whereDate('starts_at', $quarter['starts_at'])
                    ->whereDate('ends_at', $quarter['ends_at'])
                    ->first();

            if ($existingQuarter) {
                $existingQuarter->update($quarter + [
                    'is_active' => now()->betweenIncluded($quarter['starts_at'], $quarter['ends_at']),
                ]);
            } else {
                $existingQuarter = Quarter::create($quarter + [
                    'is_active' => now()->betweenIncluded($quarter['starts_at'], $quarter['ends_at']),
                ]);
            }

            Quarter::whereKeyNot($existingQuarter->id)
                ->whereDate('starts_at', $quarter['starts_at'])
                ->whereDate('ends_at', $quarter['ends_at'])
                ->get()
                ->each(function (Quarter $duplicateQuarter) use ($existingQuarter) {
                    DB::table('goals')
                        ->where('quarter_id', $duplicateQuarter->id)
                        ->update(['quarter_id' => $existingQuarter->id]);

                    DB::table('quarterly_reflections')
                        ->where('quarter_id', $duplicateQuarter->id)
                        ->update(['quarter_id' => $existingQuarter->id]);

                    $duplicateQuarter->delete();
                });
        }

        Quarter::whereNotIn('name', collect($quarters)->pluck('name')->all())
            ->whereDoesntHave('goals')
            ->delete();

        Quarter::whereNotIn('name', collect($quarters)->pluck('name')->all())
            ->update(['is_active' => false]);

        $goalPillars = [
            [
                'name' => 'Operational Excellence',
                'annual_goal' => 'Both teams operate with clear systems, shared accountability, and healthy internal cultures by year-end.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Church-First Identity',
                'annual_goal' => "Define, communicate, and operationalize ARM's identity as a church-first organization internally and externally.",
                'sort_order' => 2,
            ],
            [
                'name' => 'Financial Health & Sustainability',
                'annual_goal' => 'Close the program funding gap, diversify income, and build mutual trust through transparent, program-level financial reporting.',
                'sort_order' => 3,
            ],
            [
                'name' => 'US-Uganda Alignment Framework',
                'annual_goal' => 'How the two teams stay connected, accountable, and moving in the same direction.',
                'sort_order' => 4,
            ],
        ];

        foreach ($goalPillars as $goalPillar) {
            GoalPillar::updateOrCreate(['name' => $goalPillar['name']], $goalPillar + [
                'is_active' => true,
            ]);
        }

        $permissions = [
            'manage departments',
            'manage goal pillars',
            'manage sections',
            'manage units',
            'manage users',
            'manage quarters',
            'manage goals',
            'review goals',
            'submit daily reports',
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
            'submit daily reports',
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
        $superAdmin->accessibleDepartments()->sync(Department::pluck('id')->all());
    }

    private function departmentCode(int $index): string
    {
        return (string) (100000 + $index);
    }

    private function unitCode(int $departmentIndex, int $unitIndex): string
    {
        return 'U'.str_pad((string) $departmentIndex, 2, '0', STR_PAD_LEFT).str_pad((string) $unitIndex, 2, '0', STR_PAD_LEFT);
    }

    private function sectionCode(int $departmentIndex, int|string $sectionIndex): string
    {
        $index = is_int($sectionIndex) ? $sectionIndex + 1 : crc32($sectionIndex) % 90 + 10;

        return 'S'.str_pad((string) $departmentIndex, 2, '0', STR_PAD_LEFT).str_pad((string) $index, 2, '0', STR_PAD_LEFT);
    }

    private function positionCode(int $departmentIndex, int $positionIndex): string
    {
        return 'P'.str_pad((string) $departmentIndex, 2, '0', STR_PAD_LEFT).str_pad((string) $positionIndex, 3, '0', STR_PAD_LEFT);
    }
}
