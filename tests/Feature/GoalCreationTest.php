<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Goal;
use App\Models\GoalPillar;
use App\Models\Quarter;
use App\Models\Section;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GoalCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_goal_index_links_to_separate_create_page_for_supervisor(): void
    {
        $department = Department::create(['name' => 'ICT Department']);
        $unit = Unit::create(['department_id' => $department->id, 'name' => 'Software Development Unit']);

        $supervisor = User::factory()->create([
            'department_id' => $department->id,
            'unit_id' => $unit->id,
            'role' => 'supervisor',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $response = $this->actingAs($supervisor)->get(route('goals.index'));

        $response->assertOk();
        $response->assertSee('Strategic Goals per Pillar');
        $response->assertDontSee('Pillar Planning Table');

        $this->actingAs($supervisor)->get(route('goals.create'))
            ->assertOk()
            ->assertSee('Strategic Goals per Pillar')
            ->assertSee('Pillar Planning Table')
            ->assertSee('Goal Pillars')
            ->assertSee('Key Activities')
            ->assertSee('Key Result Areas / Deliverables');
    }

    public function test_custom_role_with_manage_goals_permission_can_create_goal(): void
    {
        Permission::findOrCreate('manage goals');
        $role = Role::findOrCreate('Program Lead');
        $role->syncPermissions(['manage goals']);

        $department = Department::create(['name' => 'Programs Department']);
        $pillar = $this->goalPillar();
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);
        $user = User::factory()->create([
            'department_id' => $department->id,
            'role' => 'program_lead',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        $user->assignRole($role);

        $this->actingAs($user)->post(route('goals.store'), [
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'department_ids' => [$department->id],
            'unit_ids' => [],
            'level' => 'department',
            'title' => 'Improve Program Follow-up',
        ] + [
            'objectives' => [
                $this->objectiveFields(['title' => 'Follow-up schedule', 'weight' => 100]),
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('goals', ['title' => 'Improve Program Follow-up']);
    }

    public function test_supervisor_can_create_goal_with_objectives(): void
    {
        $department = Department::create(['name' => 'ICT Department']);
        $pillar = $this->goalPillar();
        $unit = Unit::create(['department_id' => $department->id, 'name' => 'Software Development Unit']);
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);

        $supervisor = User::factory()->create([
            'department_id' => $department->id,
            'unit_id' => $unit->id,
            'role' => 'supervisor',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $response = $this->actingAs($supervisor)->post(route('goals.store'), [
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'department_ids' => [$department->id],
            'unit_ids' => [$unit->id],
            'level' => 'unit',
            'title' => 'Improve ICT Service Delivery',
        ] + [
            'objectives' => [
                $this->objectiveFields(['title' => 'Upgrade Staff Computers', 'key_activities' => ['Procure replacement computers.', 'Configure replacement computers.'], 'specific_output' => 'Replace old machines.', 'weight' => 40, 'starts_at' => '2026-01-01', 'due_at' => '2026-01-21', 'planned_weeks' => 3]),
                $this->objectiveFields(['title' => 'Improve Internet Stability', 'key_activities' => ['Audit outages.', 'Configure backup routing.'], 'specific_output' => 'Reduce downtime.', 'weight' => 60, 'starts_at' => '2026-01-22', 'due_at' => '2026-02-11', 'planned_weeks' => 3]),
            ],
        ]);

        $goal = Goal::where('title', 'Improve ICT Service Delivery')->first();

        $response->assertRedirect(route('goals.show', $goal));
        $this->assertNotNull($goal);
        $this->assertSame($pillar->id, $goal->goal_pillar_id);
        $this->assertSame(2, $goal->objectives()->count());
        $this->assertSame(100, (int) $goal->objectives()->sum('weight'));
        $this->assertSame(
            ['Procure replacement computers.', 'Configure replacement computers.'],
            $goal->objectives()->where('title', 'Upgrade Staff Computers')->firstOrFail()->keyActivitiesList()
        );
        $this->assertTrue($goal->assignedDepartments()->whereKey($department->id)->exists());
        $this->assertTrue($goal->assignedUnits()->whereKey($unit->id)->exists());
    }

    public function test_goal_objective_weights_must_total_one_hundred(): void
    {
        $department = Department::create(['name' => 'ICT Department']);
        $pillar = $this->goalPillar();
        $unit = Unit::create(['department_id' => $department->id, 'name' => 'Software Development Unit']);
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);

        $supervisor = User::factory()->create([
            'department_id' => $department->id,
            'unit_id' => $unit->id,
            'role' => 'supervisor',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $response = $this->actingAs($supervisor)->post(route('goals.store'), [
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'department_ids' => [$department->id],
            'unit_ids' => [$unit->id],
            'level' => 'unit',
            'title' => 'Improve ICT Service Delivery',
        ] + [
            'objectives' => [
                $this->objectiveFields(['title' => 'Upgrade Staff Computers', 'weight' => 40, 'starts_at' => '2026-01-01', 'due_at' => '2026-01-21', 'planned_weeks' => 3]),
                $this->objectiveFields(['title' => 'Improve Internet Stability', 'weight' => 40, 'starts_at' => '2026-01-22', 'due_at' => '2026-02-11', 'planned_weeks' => 3]),
            ],
        ]);

        $response->assertSessionHasErrors('objectives');
        $this->assertDatabaseMissing('goals', ['title' => 'Improve ICT Service Delivery']);
    }

    public function test_goal_can_be_assigned_to_multiple_departments_and_edited_with_objectives(): void
    {
        $ict = Department::create(['name' => 'ICT Department']);
        $pillar = $this->goalPillar();
        $updatedPillar = $this->goalPillar([
            'name' => 'Church-First Identity',
            'annual_goal' => "Define, communicate, and operationalize ARM's identity as a church-first organization internally and externally.",
            'sort_order' => 2,
        ]);
        $finance = Department::create(['name' => 'Finance Department']);
        $ictUnit = Unit::create(['department_id' => $ict->id, 'name' => 'Software Development Unit']);
        $financeUnit = Unit::create(['department_id' => $finance->id, 'name' => 'Accounts Unit']);
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);

        $admin = User::factory()->create([
            'department_id' => $ict->id,
            'unit_id' => $ictUnit->id,
            'role' => 'admin',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('goals.store'), [
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'department_ids' => [$ict->id, $finance->id],
            'unit_ids' => [$ictUnit->id, $financeUnit->id],
            'level' => 'unit',
            'title' => 'Cross Department Controls',
        ] + [
            'objectives' => [
                $this->objectiveFields(['title' => 'Policy rollout', 'weight' => 50, 'starts_at' => '2026-01-01', 'due_at' => '2026-01-14', 'planned_weeks' => 2]),
                $this->objectiveFields(['title' => 'Training', 'weight' => 50, 'starts_at' => '2026-01-15', 'due_at' => '2026-01-28', 'planned_weeks' => 2]),
            ],
        ])->assertRedirect();

        $goal = Goal::where('title', 'Cross Department Controls')->firstOrFail();
        $firstObjective = $goal->objectives()->first();
        $this->assertSame($pillar->id, $goal->goal_pillar_id);

        $this->actingAs($admin)->put(route('goals.update', $goal), [
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $updatedPillar->id,
            'department_ids' => [$ict->id, $finance->id],
            'unit_ids' => [$ictUnit->id],
            'level' => 'unit',
            'title' => 'Cross Department Controls Updated',
        ] + [
            'objectives' => [
                $this->objectiveFields(['id' => $firstObjective->id, 'title' => 'Policy rollout updated', 'weight' => 40, 'starts_at' => '2026-01-01', 'due_at' => '2026-01-14', 'planned_weeks' => 2]),
                $this->objectiveFields(['title' => 'Evidence review', 'weight' => 60, 'starts_at' => '2026-01-15', 'due_at' => '2026-01-28', 'planned_weeks' => 2]),
            ],
        ])->assertRedirect(route('goals.show', $goal));

        $goal->refresh();

        $this->assertSame('Cross Department Controls Updated', $goal->title);
        $this->assertSame($updatedPillar->id, $goal->goal_pillar_id);
        $this->assertSame(2, $goal->objectives()->count());
        $this->assertSame(100, (int) $goal->objectives()->sum('weight'));
        $this->assertTrue($goal->assignedDepartments()->whereKey($finance->id)->exists());
        $this->assertTrue($goal->assignedUnits()->whereKey($ictUnit->id)->exists());
        $this->assertFalse($goal->assignedUnits()->whereKey($financeUnit->id)->exists());
    }

    public function test_unit_goal_does_not_store_duplicate_parent_section_assignment(): void
    {
        $department = Department::create(['name' => 'ICT Department']);
        $pillar = $this->goalPillar();
        $section = Section::create(['department_id' => $department->id, 'name' => 'Software Development']);
        $unit = Unit::create(['department_id' => $department->id, 'section_id' => $section->id, 'name' => 'Applications Unit']);
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);

        $admin = User::factory()->create([
            'department_id' => $department->id,
            'unit_id' => $unit->id,
            'role' => 'admin',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('goals.store'), [
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'department_ids' => [$department->id],
            'unit_ids' => [$unit->id],
            'level' => 'unit',
            'title' => 'Build Applications Workflow',
        ] + [
            'objectives' => [
                $this->objectiveFields(['title' => 'Workflow delivery', 'weight' => 100]),
            ],
        ])->assertRedirect();

        $goal = Goal::where('title', 'Build Applications Workflow')->firstOrFail();

        $this->assertSame(1, $goal->assignments()->count());
        $this->assertTrue($goal->assignedUnits()->whereKey($unit->id)->exists());
        $this->assertTrue($goal->assignedSections()->whereKey($section->id)->exists());
    }

    private function goalPillar(array $overrides = []): GoalPillar
    {
        return GoalPillar::create($overrides + [
            'name' => 'Operational Excellence',
            'annual_goal' => 'Both teams operate with clear systems, shared accountability, and healthy internal cultures by year-end.',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    private function objectiveFields(array $overrides = []): array
    {
        return $overrides + [
            'title' => 'Objective',
            'key_activities' => ['Complete the listed activities for this objective.'],
            'specific_output' => 'Complete a clearly defined objective output with submitted evidence.',
            'weight' => 100,
            'planned_weeks' => 1,
            'reporting_frequency' => ['weekly'],
            'starts_at' => '2026-01-01',
            'due_at' => '2026-01-07',
        ];
    }
}
