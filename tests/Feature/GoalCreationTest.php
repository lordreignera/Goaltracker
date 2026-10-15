<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Goal;
use App\Models\GoalPillar;
use App\Models\Quarter;
use App\Models\Section;
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
        $section = $department->sections()->create(['name' => 'Software Development']);

        $supervisor = User::factory()->create([
            'department_id' => $department->id,
            'section_id' => $section->id,
            'role' => 'supervisor',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $response = $this->actingAs($supervisor)->get(route('goals.index'));

        $response->assertOk();
        $response->assertSee('All Visible Goals');
        $response->assertDontSee('Pillar Planning Table');

        $this->actingAs($supervisor)->get(route('goals.create'))
            ->assertOk()
            ->assertSee('Work Plan Goals by Activity Pillar')
            ->assertSee('Pillar Planning Table')
            ->assertSee('Activity Pillar')
            ->assertSee('Office / Department')
            ->assertSee('Select all departments')
            ->assertSee('Objective Weight Total')
            ->assertSee('If there is one objective, give it 100%.')
            ->assertSee('Goal Name')
            ->assertSee('Key Activities')
            ->assertSee('Deliverable')
            ->assertSee('Save Draft')
            ->assertSee('Save Goal')
            ->assertDontSee('Goal Set Name')
            ->assertDontSee('Goal Level')
            ->assertDontSee('Unit');
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
            'level' => 'department',
            'title' => 'Improve Program Follow-up',
        ] + [
            'objectives' => [
                $this->objectiveFields(['title' => 'Follow-up schedule', 'weight' => 100]),
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('goal_objectives', ['title' => 'Follow-up schedule']);
    }

    public function test_supervisor_can_create_goal_with_objectives(): void
    {
        $department = Department::create(['name' => 'ICT Department']);
        $pillar = $this->goalPillar();
        $section = $department->sections()->create(['name' => 'Software Development']);
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);

        $supervisor = User::factory()->create([
            'department_id' => $department->id,
            'section_id' => $section->id,
            'role' => 'supervisor',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $response = $this->actingAs($supervisor)->post(route('goals.store'), [
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'department_ids' => [$department->id],
            'section_ids' => [$section->id],
            'level' => 'section',
            'title' => 'Improve ICT Service Delivery',
        ] + [
            'objectives' => [
                $this->objectiveFields(['title' => 'Upgrade Staff Computers', 'key_activities' => ['Procure replacement computers.', 'Configure replacement computers.'], 'specific_output' => 'Replace old machines.', 'weight' => 40, 'starts_at' => '2026-01-01', 'due_at' => '2026-01-21', 'planned_weeks' => 3]),
                $this->objectiveFields(['title' => 'Improve Internet Stability', 'key_activities' => ['Audit outages.', 'Configure backup routing.'], 'specific_output' => 'Reduce downtime.', 'weight' => 60, 'starts_at' => '2026-01-22', 'due_at' => '2026-02-11', 'planned_weeks' => 3]),
            ],
        ]);

        $goal = Goal::whereHas('objectives', fn ($query) => $query->where('title', 'Upgrade Staff Computers'))->first();

        $response->assertRedirect(route('goals.show', $goal));
        $this->assertNotNull($goal);
        $this->assertSame($pillar->id, $goal->goal_pillar_id);
        $this->assertSame('draft', $goal->status);
        $this->assertSame(2, $goal->objectives()->count());
        $this->assertSame(100, (int) $goal->objectives()->sum('weight'));
        $this->assertSame(
            ['Procure replacement computers.', 'Configure replacement computers.'],
            $goal->objectives()->where('title', 'Upgrade Staff Computers')->firstOrFail()->keyActivitiesList()
        );
        $this->assertTrue($goal->assignedDepartments()->whereKey($department->id)->exists());
        $this->assertFalse($goal->assignedSections()->exists());
    }

    public function test_save_goal_creates_a_submitted_work_plan(): void
    {
        $department = Department::create(['name' => 'Programs Department']);
        $pillar = $this->goalPillar();
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);
        $user = User::factory()->create([
            'department_id' => $department->id,
            'role' => 'staff',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('goals.store'), [
            'save_action' => 'submit',
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'department_ids' => [$department->id],
            'objectives' => [
                $this->objectiveFields(['title' => 'Train program leaders', 'weight' => 100]),
            ],
        ]);

        $goal = Goal::whereHas('objectives', fn ($query) => $query->where('title', 'Train program leaders'))->firstOrFail();

        $response->assertRedirect(route('goals.show', $goal));
        $response->assertSessionHas('status', 'Office work plan saved and submitted for review.');
        $this->assertSame('submitted', $goal->status);
        $this->assertNotNull($goal->submitted_at);
    }

    public function test_goal_objective_weights_must_total_one_hundred(): void
    {
        $department = Department::create(['name' => 'ICT Department']);
        $pillar = $this->goalPillar();
        $section = $department->sections()->create(['name' => 'Software Development']);
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);

        $supervisor = User::factory()->create([
            'department_id' => $department->id,
            'section_id' => $section->id,
            'role' => 'supervisor',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $response = $this->actingAs($supervisor)->post(route('goals.store'), [
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'department_ids' => [$department->id],
            'section_ids' => [$section->id],
            'level' => 'section',
            'title' => 'Improve ICT Service Delivery',
        ] + [
            'objectives' => [
                $this->objectiveFields(['title' => 'Upgrade Staff Computers', 'weight' => 40, 'starts_at' => '2026-01-01', 'due_at' => '2026-01-21', 'planned_weeks' => 3]),
                $this->objectiveFields(['title' => 'Improve Internet Stability', 'weight' => 40, 'starts_at' => '2026-01-22', 'due_at' => '2026-02-11', 'planned_weeks' => 3]),
            ],
        ]);

        $response->assertSessionHasErrors([
            'objectives' => 'Objective weights must total exactly 100%. Current total is 80%.',
        ]);
        $this->assertSame(0, Goal::count());
    }

    public function test_department_work_plan_can_be_edited_with_goals(): void
    {
        $ict = Department::create(['name' => 'ICT Department']);
        $pillar = $this->goalPillar();
        $updatedPillar = $this->goalPillar([
            'name' => 'Church-First Identity',
            'annual_goal' => "Define, communicate, and operationalize ARM's identity as a church-first organization internally and externally.",
            'sort_order' => 2,
        ]);
        $ictSection = $ict->sections()->create(['name' => 'Software Development']);
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);

        $admin = User::factory()->create([
            'department_id' => $ict->id,
            'section_id' => $ictSection->id,
            'role' => 'admin',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('goals.store'), [
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'department_ids' => [$ict->id],
            'section_ids' => [$ictSection->id],
            'level' => 'section',
            'title' => 'Cross Department Controls',
        ] + [
            'objectives' => [
                $this->objectiveFields(['title' => 'Policy rollout', 'weight' => 50, 'starts_at' => '2026-01-01', 'due_at' => '2026-01-14', 'planned_weeks' => 2]),
                $this->objectiveFields(['title' => 'Training', 'weight' => 50, 'starts_at' => '2026-01-15', 'due_at' => '2026-01-28', 'planned_weeks' => 2]),
            ],
        ])->assertRedirect();

        $goal = Goal::whereHas('objectives', fn ($query) => $query->where('title', 'Policy rollout'))->firstOrFail();
        $firstObjective = $goal->objectives()->first();
        $this->assertSame($pillar->id, $goal->goal_pillar_id);

        $this->actingAs($admin)->put(route('goals.update', $goal), [
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $updatedPillar->id,
            'department_ids' => [$ict->id],
            'section_ids' => [$ictSection->id],
            'level' => 'section',
            'title' => 'Cross Department Controls Updated',
        ] + [
            'objectives' => [
                $this->objectiveFields(['id' => $firstObjective->id, 'title' => 'Policy rollout updated', 'weight' => 40, 'starts_at' => '2026-01-01', 'due_at' => '2026-01-14', 'planned_weeks' => 2]),
                $this->objectiveFields(['title' => 'Evidence review', 'weight' => 60, 'starts_at' => '2026-01-15', 'due_at' => '2026-01-28', 'planned_weeks' => 2]),
            ],
        ])->assertRedirect(route('goals.show', $goal));

        $goal->refresh();

        $this->assertSame($updatedPillar->id, $goal->goal_pillar_id);
        $this->assertSame(2, $goal->objectives()->count());
        $this->assertSame(100, (int) $goal->objectives()->sum('weight'));
        $this->assertTrue($goal->assignedDepartments()->whereKey($ict->id)->exists());
    }

    public function test_office_work_plan_stores_one_department_assignment(): void
    {
        $department = Department::create(['name' => 'ICT Department']);
        $pillar = $this->goalPillar();
        $section = Section::create(['department_id' => $department->id, 'name' => 'Software Development']);
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);

        $admin = User::factory()->create([
            'department_id' => $department->id,
            'section_id' => $section->id,
            'role' => 'admin',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('goals.store'), [
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'department_ids' => [$department->id],
            'section_ids' => [$section->id],
            'level' => 'section',
            'title' => 'Build Applications Workflow',
        ] + [
            'objectives' => [
                $this->objectiveFields(['title' => 'Workflow delivery', 'weight' => 100]),
            ],
        ])->assertRedirect();

        $goal = Goal::whereHas('objectives', fn ($query) => $query->where('title', 'Workflow delivery'))->firstOrFail();

        $this->assertSame(1, $goal->assignments()->count());
        $this->assertTrue($goal->assignedDepartments()->whereKey($department->id)->exists());
    }

    public function test_admin_can_assign_an_office_work_plan_to_multiple_departments(): void
    {
        $ict = Department::create(['name' => 'ICT Department']);
        $finance = Department::create(['name' => 'Finance Department']);
        $pillar = $this->goalPillar();
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);
        $admin = User::factory()->create([
            'department_id' => $ict->id,
            'role' => 'admin',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('goals.store'), [
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'department_ids' => [$ict->id, $finance->id],
            'objectives' => [
                $this->objectiveFields(['title' => 'Shared systems rollout', 'weight' => 100]),
            ],
        ])->assertRedirect();

        $goal = Goal::whereHas('objectives', fn ($query) => $query->where('title', 'Shared systems rollout'))->firstOrFail();

        $this->assertCount(2, $goal->assignedDepartments);
        $this->assertEqualsCanonicalizing([$ict->id, $finance->id], $goal->assignedDepartments->pluck('id')->all());
    }

    public function test_supervisor_can_only_select_departments_they_can_access(): void
    {
        $ict = Department::create(['name' => 'ICT Department']);
        $finance = Department::create(['name' => 'Finance Department']);
        $pillar = $this->goalPillar();
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);
        $supervisor = User::factory()->create([
            'department_id' => $ict->id,
            'role' => 'supervisor',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $this->actingAs($supervisor)->post(route('goals.store'), [
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'department_ids' => [$ict->id, $finance->id],
            'objectives' => [
                $this->objectiveFields(['title' => 'Unauthorized shared plan', 'weight' => 100]),
            ],
        ])->assertSessionHasErrors('department_ids');

        $this->assertSame(0, Goal::count());

        $supervisor->accessibleDepartments()->sync([$finance->id]);

        $this->actingAs($supervisor)->post(route('goals.store'), [
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'department_ids' => [$ict->id, $finance->id],
            'objectives' => [
                $this->objectiveFields(['title' => 'Authorized shared plan', 'weight' => 100]),
            ],
        ])->assertRedirect();

        $goal = Goal::whereHas('objectives', fn ($query) => $query->where('title', 'Authorized shared plan'))->firstOrFail();

        $this->assertCount(2, $goal->assignedDepartments);
    }

    public function test_dashboard_places_the_pillar_table_below_kpis_with_goal_actions(): void
    {
        $department = Department::create(['name' => 'Programs Department']);
        $pillar = $this->goalPillar();
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);
        $supervisor = User::factory()->create([
            'department_id' => $department->id,
            'role' => 'supervisor',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        $goal = Goal::create([
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'created_by' => $supervisor->id,
            'owner_id' => $supervisor->id,
            'status' => 'draft',
        ]);
        $goal->assignments()->create(['department_id' => $department->id]);
        $objective = $goal->objectives()->create($this->objectiveFields([
            'title' => 'Community outreach visits',
            'key_activities' => 'Visit community partner sites.',
            'weight' => 100,
        ]) + ['status' => 'pending']);

        $this->actingAs($supervisor)->get(route('dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['Total Goals', 'Activity Pillars and Work Plan Goals', 'Goal Progress'])
            ->assertSee('Create Goal')
            ->assertSee('data-bs-target="#dashboardCreateGoalModal"', false)
            ->assertSee('data-dashboard-goal-form', false)
            ->assertSee('data-weight-summary', false)
            ->assertSee(route('goals.store'), false)
            ->assertSee('Save Draft')
            ->assertSee('Save Goal')
            ->assertSee('Community outreach visits')
            ->assertSee('Add Work Goal')
            ->assertSee('Edit')
            ->assertSee('Delete')
            ->assertSee('Add Report')
            ->assertSee('View Progress')
            ->assertSee(route('goals.destroy', $goal), false)
            ->assertSee("#report-objective-{$objective->id}", false);
    }

    public function test_objective_with_reporting_history_cannot_be_removed_from_goal(): void
    {
        $department = Department::create(['name' => 'Programs Department']);
        $pillar = $this->goalPillar();
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);
        $admin = User::factory()->create([
            'department_id' => $department->id,
            'role' => 'admin',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        $goal = Goal::create([
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'owner_id' => $admin->id,
            'created_by' => $admin->id,
            'title' => 'Preserve Reporting History',
            'level' => 'department',
        ]);
        $goal->assignments()->create(['department_id' => $department->id]);
        $objective = $goal->objectives()->create([
            'title' => 'Reported objective',
            'key_activities' => 'Complete the planned activity.',
            'specific_output' => 'A completed output.',
            'weight' => 100,
            'planned_weeks' => 1,
            'reporting_frequency' => ['weekly'],
            'starts_at' => '2026-01-01',
            'due_at' => '2026-01-07',
        ]);
        $objective->weeklyUpdates()->create([
            'user_id' => $admin->id,
            'report_date' => '2026-01-01',
            'reporting_frequency' => 'weekly',
            'achievement_summary' => 'Work was reported.',
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($admin)->put(route('goals.update', $goal), [
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'department_ids' => [$department->id],
            'section_ids' => [],
            'level' => 'department',
            'title' => 'Preserve Reporting History',
            'objectives' => [
                $this->objectiveFields(['title' => 'Replacement objective']),
            ],
        ]);

        $response->assertSessionHasErrors('objectives');
        $this->assertDatabaseHas('goal_objectives', [
            'id' => $objective->id,
            'title' => 'Reported objective',
        ]);
        $this->assertDatabaseMissing('goal_objectives', ['title' => 'Replacement objective']);
        $this->assertSame(1, $objective->weeklyUpdates()->count());
    }

    public function test_admin_can_delete_a_work_plan_without_reports(): void
    {
        $department = Department::create(['name' => 'Operations']);
        $pillar = $this->goalPillar();
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);
        $admin = User::factory()->create([
            'department_id' => $department->id,
            'role' => 'admin',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        $goal = Goal::create([
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'created_by' => $admin->id,
            'owner_id' => $admin->id,
            'status' => 'draft',
        ]);
        $goal->assignments()->create(['department_id' => $department->id]);
        $goal->objectives()->create($this->objectiveFields([
            'title' => 'Deletable objective',
            'key_activities' => 'Complete the planned task.',
        ]));

        $this->actingAs($admin)
            ->delete(route('goals.destroy', $goal))
            ->assertRedirect(route('goals.index'));

        $this->assertDatabaseMissing('goals', ['id' => $goal->id]);
    }

    public function test_work_plan_with_reports_cannot_be_deleted(): void
    {
        $department = Department::create(['name' => 'Operations']);
        $pillar = $this->goalPillar();
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);
        $admin = User::factory()->create([
            'department_id' => $department->id,
            'role' => 'admin',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        $goal = Goal::create([
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'created_by' => $admin->id,
            'owner_id' => $admin->id,
            'status' => 'submitted',
        ]);
        $goal->assignments()->create(['department_id' => $department->id]);
        $objective = $goal->objectives()->create($this->objectiveFields([
            'title' => 'Reported objective',
            'key_activities' => 'Complete the planned task.',
        ]));
        $objective->weeklyUpdates()->create([
            'user_id' => $admin->id,
            'report_date' => '2026-01-02',
            'reporting_frequency' => 'weekly',
            'report_period_start' => '2026-01-01',
            'report_period_end' => '2026-01-07',
            'achievement_summary' => 'Work completed.',
            'status' => 'submitted',
        ]);

        $this->actingAs($admin)
            ->from(route('goals.create'))
            ->delete(route('goals.destroy', $goal), ['modal_source' => 'delete_goal'])
            ->assertSessionHasErrors([
                'goal' => 'This work plan has submitted reports and cannot be deleted. Archive it instead to preserve its reporting history.',
            ]);

        $this->assertDatabaseHas('goals', ['id' => $goal->id]);
    }

    public function test_planning_table_actions_expose_objective_and_pillar_report_modals(): void
    {
        $department = Department::create(['name' => 'ICT Department']);
        $pillar = $this->goalPillar();
        $section = $department->sections()->create(['name' => 'Applications']);
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);

        $admin = User::factory()->create([
            'department_id' => $department->id,
            'section_id' => $section->id,
            'role' => 'admin',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $goal = Goal::create([
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'owner_id' => $admin->id,
            'created_by' => $admin->id,
            'title' => 'Operational Excellence Q1',
            'level' => 'section',
        ]);
        $goal->assignments()->create(['department_id' => $department->id, 'section_id' => $section->id]);
        $objective = $goal->objectives()->create($this->objectiveFields([
            'title' => 'Improve staff support systems',
            'key_activities' => "Approve refunds\nUpdate allowance balances",
        ]));
        $objective->weeklyUpdates()->create([
            'user_id' => $admin->id,
            'report_date' => '2026-01-02',
            'reporting_frequency' => 'weekly',
            'report_period_start' => '2026-01-01',
            'report_period_end' => '2026-01-07',
            'achievement_summary' => 'Appointments letters were worked on.',
            'action_points' => 'Orient new staff.',
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($admin)->get(route('goals.create'));

        $response->assertOk()
            ->assertSee('Add Work Goal')
            ->assertSee('Edit')
            ->assertSee('Delete')
            ->assertSee('Add Report')
            ->assertSee('View Progress')
            ->assertSee(route('goals.destroy', $goal), false)
            ->assertSee("pillarReportsModal-{$pillar->id}", false)
            ->assertSee("reportObjectiveModal-{$objective->id}", false)
            ->assertSee(route('objectives.weekly-updates.store', $objective), false)
            ->assertSee('Appointments letters were worked on.')
            ->assertSee('Orient new staff.')
            ->assertSee('modal-fullscreen-sm-down');
    }

    public function test_goal_update_rejects_the_same_objective_id_twice(): void
    {
        $department = Department::create(['name' => 'Operations']);
        $pillar = $this->goalPillar();
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);
        $admin = User::factory()->create([
            'role' => 'admin',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        $goal = Goal::create([
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'created_by' => $admin->id,
            'owner_id' => $admin->id,
        ]);
        $goal->assignments()->create(['department_id' => $department->id]);
        $objective = $goal->objectives()->create($this->objectiveFields([
            'key_activities' => 'Complete the listed activities for this objective.',
        ]));
        $duplicateObjective = $this->objectiveFields([
            'id' => $objective->id,
            'weight' => 50,
        ]);

        $this->actingAs($admin)->put(route('goals.update', $goal), [
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'department_ids' => [$department->id],
            'objectives' => [$duplicateObjective, $duplicateObjective],
        ])->assertSessionHasErrors('objectives.1.id');

        $this->assertSame(100, (int) $goal->objectives()->sum('weight'));
    }

    public function test_malformed_objective_dates_return_validation_errors(): void
    {
        $department = Department::create(['name' => 'Programs']);
        $pillar = $this->goalPillar();
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);
        $admin = User::factory()->create([
            'role' => 'admin',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('goals.store'), [
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'department_ids' => [$department->id],
            'objectives' => [$this->objectiveFields(['starts_at' => 'not-a-date'])],
        ])->assertSessionHasErrors('objectives.0.starts_at');

        $this->assertDatabaseCount('goals', 0);
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
