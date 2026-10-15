<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Goal;
use App\Models\GoalPillar;
use App\Models\Quarter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_dashboard_hides_administration_navigation(): void
    {
        Role::findOrCreate('Staff');

        $staff = User::factory()->create([
            'role' => 'staff',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        $staff->assignRole('Staff');

        $response = $this->actingAs($staff)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('Goals');
        $response->assertSee('Create Goal');
        $response->assertSee('Work Plan Goals');
        $response->assertDontSee('User Management');
        $response->assertDontSee('Departments');
        $response->assertDontSee('Positions');
        $response->assertDontSee('Roles &amp; Permissions', false);
        $response->assertDontSee('Quarters');
        $response->assertDontSee('Audit Logs');
    }

    public function test_staff_cannot_open_administration_routes_directly(): void
    {
        Role::findOrCreate('Staff');

        $staff = User::factory()->create([
            'role' => 'staff',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        $staff->assignRole('Staff');

        $this->actingAs($staff)->get(route('users.management.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('departments.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('positions.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('roles.management.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('quarters.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('audit-logs.index'))->assertForbidden();
    }

    public function test_staff_only_sees_goals_for_their_department_or_section(): void
    {
        Role::findOrCreate('Staff');

        $department = Department::create(['name' => 'ICT Department']);
        $otherDepartment = Department::create(['name' => 'Finance Department']);
        $section = $department->sections()->create(['name' => 'Software Development']);
        $otherSection = $department->sections()->create(['name' => 'Infrastructure']);
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);

        $staff = User::factory()->create([
            'department_id' => $department->id,
            'section_id' => $section->id,
            'role' => 'staff',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        $staff->assignRole('Staff');

        $departmentGoal = Goal::create([
            'quarter_id' => $quarter->id,
            'status' => 'submitted',
        ]);
        $departmentGoal->assignments()->create(['department_id' => $department->id]);

        $ownSectionGoal = Goal::create([
            'quarter_id' => $quarter->id,
            'status' => 'submitted',
        ]);
        $ownSectionGoal->assignments()->create(['department_id' => $department->id, 'section_id' => $section->id]);

        $otherSectionGoal = Goal::create([
            'quarter_id' => $quarter->id,
            'status' => 'submitted',
        ]);
        $otherSectionGoal->assignments()->create(['department_id' => $department->id, 'section_id' => $otherSection->id]);

        $otherDepartmentGoal = Goal::create([
            'quarter_id' => $quarter->id,
            'status' => 'submitted',
        ]);
        $otherDepartmentGoal->assignments()->create(['department_id' => $otherDepartment->id]);

        $response = $this->actingAs($staff)->get(route('goals.index'));

        $response->assertOk();
        $response->assertSee(route('goals.show', $departmentGoal), false);
        $response->assertSee(route('goals.show', $ownSectionGoal), false);
        $response->assertDontSee(route('goals.show', $otherSectionGoal), false);
        $response->assertDontSee(route('goals.show', $otherDepartmentGoal), false);
        $response->assertSee('Create Goal');

        $this->actingAs($staff)
            ->delete(route('goals.destroy', $departmentGoal))
            ->assertForbidden();
        $this->assertDatabaseHas('goals', ['id' => $departmentGoal->id]);
    }

    public function test_staff_can_create_edit_and_submit_their_own_department_goal(): void
    {
        Role::findOrCreate('Staff');

        $department = Department::create(['name' => 'Programs']);
        $otherDepartment = Department::create(['name' => 'Finance']);
        $quarter = Quarter::create([
            'name' => 'Q1 2026',
            'starts_at' => '2026-01-01',
            'ends_at' => '2026-03-31',
            'is_active' => true,
        ]);
        $pillar = GoalPillar::create([
            'name' => 'Operational Excellence',
            'annual_goal' => 'Strengthen accountable operating systems.',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $staff = User::factory()->create([
            'department_id' => $department->id,
            'role' => 'staff',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        $staff->assignRole('Staff');

        $this->actingAs($staff)
            ->get(route('goals.create'))
            ->assertOk()
            ->assertSee('Add Work Goal');

        $payload = [
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'department_ids' => [$department->id],
            'objectives' => [[
                'title' => 'Complete the quarterly work plan',
                'key_activities' => ['Prepare activities', 'Confirm deliverables'],
                'specific_output' => 'Approved quarterly work plan.',
                'weight' => 100,
                'planned_weeks' => 2,
                'reporting_frequency' => ['weekly'],
                'starts_at' => '2026-01-01',
                'due_at' => '2026-01-14',
            ]],
        ];

        $this->actingAs($staff)
            ->post(route('goals.store'), $payload)
            ->assertRedirect();

        $goal = Goal::where('created_by', $staff->id)->firstOrFail();

        $this->actingAs($staff)->get(route('goals.edit', $goal))->assertOk();
        $this->actingAs($staff)->put(route('goals.update', $goal), $payload)->assertRedirect();
        $this->actingAs($staff)->post(route('goals.submit', $goal))->assertRedirect();

        $this->assertSame('submitted', $goal->fresh()->status);
        $this->actingAs($staff)->get(route('goals.edit', $goal))->assertForbidden();

        $payload['department_ids'] = [$otherDepartment->id];
        $this->actingAs($staff)
            ->post(route('goals.store'), $payload)
            ->assertSessionHasErrors('department_ids');
    }
}
