<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Goal;
use App\Models\Quarter;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $response->assertSee('Create Goal');
        $response->assertDontSee('Create Main Goal');

        $this->actingAs($supervisor)->get(route('goals.create'))
            ->assertOk()
            ->assertSee('Create Main Goal')
            ->assertSee('Objectives / Sub-Goals');
    }

    public function test_supervisor_can_create_goal_with_objectives(): void
    {
        $department = Department::create(['name' => 'ICT Department']);
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
            'department_ids' => [$department->id],
            'unit_ids' => [$unit->id],
            'level' => 'unit',
            'title' => 'Improve ICT Service Delivery',
            'description' => 'Better support response across the unit.',
            'objectives' => [
                ['title' => 'Upgrade Staff Computers', 'description' => 'Replace old machines.', 'weight' => 40, 'starts_at' => '2026-01-01', 'due_at' => '2026-01-21'],
                ['title' => 'Improve Internet Stability', 'description' => 'Reduce downtime.', 'weight' => 60, 'starts_at' => '2026-01-22', 'due_at' => '2026-02-11'],
            ],
        ]);

        $goal = Goal::where('title', 'Improve ICT Service Delivery')->first();

        $response->assertRedirect(route('goals.show', $goal));
        $this->assertNotNull($goal);
        $this->assertSame(2, $goal->objectives()->count());
        $this->assertSame(100, (int) $goal->objectives()->sum('weight'));
        $this->assertTrue($goal->assignedDepartments()->whereKey($department->id)->exists());
        $this->assertTrue($goal->assignedUnits()->whereKey($unit->id)->exists());
    }

    public function test_goal_objective_weights_must_total_one_hundred(): void
    {
        $department = Department::create(['name' => 'ICT Department']);
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
            'department_ids' => [$department->id],
            'unit_ids' => [$unit->id],
            'level' => 'unit',
            'title' => 'Improve ICT Service Delivery',
            'objectives' => [
                ['title' => 'Upgrade Staff Computers', 'weight' => 40, 'starts_at' => '2026-01-01', 'due_at' => '2026-01-21'],
                ['title' => 'Improve Internet Stability', 'weight' => 40, 'starts_at' => '2026-01-22', 'due_at' => '2026-02-11'],
            ],
        ]);

        $response->assertSessionHasErrors('objectives');
        $this->assertDatabaseMissing('goals', ['title' => 'Improve ICT Service Delivery']);
    }

    public function test_goal_can_be_assigned_to_multiple_departments_and_edited_with_objectives(): void
    {
        $ict = Department::create(['name' => 'ICT Department']);
        $finance = Department::create(['name' => 'Finance Department']);
        $ictUnit = Unit::create(['department_id' => $ict->id, 'name' => 'Software Development Unit']);
        $financeUnit = Unit::create(['department_id' => $finance->id, 'name' => 'Accounts Unit']);
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);

        $supervisor = User::factory()->create([
            'department_id' => $ict->id,
            'unit_id' => $ictUnit->id,
            'role' => 'supervisor',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $this->actingAs($supervisor)->post(route('goals.store'), [
            'quarter_id' => $quarter->id,
            'department_ids' => [$ict->id, $finance->id],
            'unit_ids' => [$ictUnit->id, $financeUnit->id],
            'level' => 'unit',
            'title' => 'Cross Department Controls',
            'objectives' => [
                ['title' => 'Policy rollout', 'weight' => 50, 'starts_at' => '2026-01-01', 'due_at' => '2026-01-14'],
                ['title' => 'Training', 'weight' => 50, 'starts_at' => '2026-01-15', 'due_at' => '2026-01-28'],
            ],
        ])->assertRedirect();

        $goal = Goal::where('title', 'Cross Department Controls')->firstOrFail();
        $firstObjective = $goal->objectives()->first();

        $this->actingAs($supervisor)->put(route('goals.update', $goal), [
            'quarter_id' => $quarter->id,
            'department_ids' => [$ict->id, $finance->id],
            'unit_ids' => [$ictUnit->id],
            'level' => 'unit',
            'title' => 'Cross Department Controls Updated',
            'objectives' => [
                ['id' => $firstObjective->id, 'title' => 'Policy rollout updated', 'weight' => 40, 'starts_at' => '2026-01-01', 'due_at' => '2026-01-14'],
                ['title' => 'Evidence review', 'weight' => 60, 'starts_at' => '2026-01-15', 'due_at' => '2026-01-28'],
            ],
        ])->assertRedirect(route('goals.show', $goal));

        $goal->refresh();

        $this->assertSame('Cross Department Controls Updated', $goal->title);
        $this->assertSame(2, $goal->objectives()->count());
        $this->assertSame(100, (int) $goal->objectives()->sum('weight'));
        $this->assertTrue($goal->assignedDepartments()->whereKey($finance->id)->exists());
        $this->assertTrue($goal->assignedUnits()->whereKey($ictUnit->id)->exists());
        $this->assertFalse($goal->assignedUnits()->whereKey($financeUnit->id)->exists());
    }
}
