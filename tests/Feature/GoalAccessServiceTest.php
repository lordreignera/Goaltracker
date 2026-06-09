<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Goal;
use App\Models\Quarter;
use App\Models\Unit;
use App\Models\User;
use App\Services\GoalAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GoalAccessServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_goal_visibility_is_limited_to_user_department_and_unit_scope(): void
    {
        Role::findOrCreate('Super Admin');
        Role::findOrCreate('Supervisor');
        Role::findOrCreate('Staff');

        $department = Department::create(['name' => 'ICT Department']);
        $otherDepartment = Department::create(['name' => 'Finance Department']);
        $unit = Unit::create(['department_id' => $department->id, 'name' => 'Software Development Unit']);
        $otherUnit = Unit::create(['department_id' => $department->id, 'name' => 'Infrastructure Unit']);
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);

        $goal = Goal::create([
            'quarter_id' => $quarter->id,
            'department_id' => $department->id,
            'unit_id' => $unit->id,
            'title' => 'Improve ICT Service Delivery',
        ]);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $unitSupervisor = User::factory()->create(['department_id' => $department->id, 'unit_id' => $unit->id, 'role' => 'supervisor']);
        $unitSupervisor->assignRole('Supervisor');

        $otherUnitSupervisor = User::factory()->create(['department_id' => $department->id, 'unit_id' => $otherUnit->id, 'role' => 'supervisor']);
        $otherUnitSupervisor->assignRole('Supervisor');

        $otherDepartmentSupervisor = User::factory()->create(['department_id' => $otherDepartment->id, 'role' => 'supervisor']);
        $otherDepartmentSupervisor->assignRole('Supervisor');

        $staff = User::factory()->create(['department_id' => $department->id, 'unit_id' => $unit->id, 'role' => 'staff']);
        $staff->assignRole('Staff');

        $service = app(GoalAccessService::class);

        $this->assertTrue($service->canViewGoal($superAdmin, $goal));
        $this->assertTrue($service->canViewGoal($unitSupervisor, $goal));
        $this->assertFalse($service->canViewGoal($otherUnitSupervisor, $goal));
        $this->assertFalse($service->canViewGoal($otherDepartmentSupervisor, $goal));
        $this->assertTrue($service->canViewGoal($staff, $goal));

        $goal->update(['unit_id' => $otherUnit->id]);

        $this->assertFalse($service->canViewGoal($staff, $goal));
    }
}
