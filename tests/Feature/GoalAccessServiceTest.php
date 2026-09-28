<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Goal;
use App\Models\Quarter;
use App\Models\User;
use App\Services\GoalAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GoalAccessServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_goal_visibility_is_limited_to_user_department_and_section_scope(): void
    {
        Role::findOrCreate('Super Admin');
        Role::findOrCreate('Supervisor');
        Role::findOrCreate('Staff');

        $department = Department::create(['name' => 'ICT Department']);
        $otherDepartment = Department::create(['name' => 'Finance Department']);
        $section = $department->sections()->create(['name' => 'Software Development']);
        $otherSection = $department->sections()->create(['name' => 'Infrastructure']);
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);

        $goal = Goal::create([
            'quarter_id' => $quarter->id,
            'status' => 'submitted',
        ]);
        $goal->assignments()->create(['department_id' => $department->id, 'section_id' => $section->id]);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $sectionSupervisor = User::factory()->create(['department_id' => $department->id, 'section_id' => $section->id, 'role' => 'supervisor']);
        $sectionSupervisor->assignRole('Supervisor');

        $otherSectionSupervisor = User::factory()->create(['department_id' => $department->id, 'section_id' => $otherSection->id, 'role' => 'supervisor']);
        $otherSectionSupervisor->assignRole('Supervisor');

        $otherDepartmentSupervisor = User::factory()->create(['department_id' => $otherDepartment->id, 'role' => 'supervisor']);
        $otherDepartmentSupervisor->assignRole('Supervisor');

        $staff = User::factory()->create(['department_id' => $department->id, 'section_id' => $section->id, 'role' => 'staff']);
        $staff->assignRole('Staff');

        $service = app(GoalAccessService::class);

        $this->assertTrue($service->canViewGoal($superAdmin, $goal));
        $this->assertTrue($service->canViewGoal($sectionSupervisor, $goal));
        $this->assertFalse($service->canViewGoal($otherSectionSupervisor, $goal));
        $this->assertFalse($service->canViewGoal($otherDepartmentSupervisor, $goal));
        $this->assertTrue($service->canViewGoal($staff, $goal));

        $goal->assignments()->delete();
        $goal->assignments()->create(['department_id' => $department->id, 'section_id' => $otherSection->id]);
        $goal->refresh();

        $this->assertFalse($service->canViewGoal($staff, $goal));
    }

    public function test_owner_field_does_not_bypass_department_or_section_visibility(): void
    {
        Role::findOrCreate('Staff');

        $department = Department::create(['name' => 'ICT Department']);
        $otherDepartment = Department::create(['name' => 'Finance Department']);
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);
        $staff = User::factory()->create([
            'department_id' => $department->id,
            'role' => 'staff',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        $staff->assignRole('Staff');

        $goal = Goal::create([
            'quarter_id' => $quarter->id,
            'owner_id' => $staff->id,
            'status' => 'submitted',
        ]);
        $goal->assignments()->create(['department_id' => $otherDepartment->id]);

        $service = app(GoalAccessService::class);

        $this->assertFalse($service->canViewGoal($staff, $goal));
        $this->assertFalse($service->canUpdateGoal($staff, $goal));
    }

    public function test_directly_assigned_user_without_a_department_can_view_goal(): void
    {
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);
        $user = User::factory()->create([
            'department_id' => null,
            'section_id' => null,
        ]);
        $goal = Goal::create([
            'quarter_id' => $quarter->id,
            'status' => 'submitted',
        ]);
        $goal->assignments()->create(['user_id' => $user->id]);

        $service = app(GoalAccessService::class);

        $this->assertTrue($service->canViewGoal($user, $goal));
        $this->assertTrue(Goal::visibleTo($user)->whereKey($goal)->exists());
    }

    public function test_draft_is_private_to_its_creator_until_submitted(): void
    {
        $department = Department::create(['name' => 'Programs']);
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);
        $creator = User::factory()->create(['department_id' => $department->id]);
        $colleague = User::factory()->create(['department_id' => $department->id]);
        $goal = Goal::create([
            'quarter_id' => $quarter->id,
            'created_by' => $creator->id,
            'status' => 'draft',
        ]);
        $goal->assignments()->create(['department_id' => $department->id]);

        $service = app(GoalAccessService::class);

        $this->assertTrue($service->canViewGoal($creator, $goal));
        $this->assertFalse($service->canViewGoal($colleague, $goal));
        $this->assertTrue(Goal::visibleTo($creator)->whereKey($goal)->exists());
        $this->assertFalse(Goal::visibleTo($colleague)->whereKey($goal)->exists());

        $goal->update(['status' => 'submitted']);

        $this->assertTrue($service->canViewGoal($colleague, $goal->fresh()));
    }
}
