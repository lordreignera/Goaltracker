<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Goal;
use App\Models\Quarter;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KeyActionStepsArrayTest extends TestCase
{
    use RefreshDatabase;

    public function test_goal_accepts_key_action_steps_as_array(): void
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

        $payload = [
            'quarter_id' => $quarter->id,
            'department_ids' => [$department->id],
            'unit_ids' => [$unit->id],
            'level' => 'unit',
            'title' => 'Save Key Action Steps Array',
            'specific' => 'Improve service delivery for assigned teams.',
            'measurable' => 'Success is measured by completed approved objective reports.',
            'achievable' => 'The work is realistic within the quarter and staffing plan.',
            'relevant' => 'The goal supports department accountability and mission delivery.',
            'time_bound' => 'The goal will be completed before the end of the quarter.',
            'key_action_steps' => ['Plan work', 'Execute objectives', 'Report weekly'],
            'primary_metric' => 'Approved objective reporting coverage',
            'deadline' => '2026-03-31',
            'objectives' => [
                [
                    'title' => 'Objective 1',
                    'specific_output' => 'Complete the task',
                    'success_measure' => 'Supervisor verifies evidence',
                    'weight' => 100,
                    'planned_weeks' => 1,
                    'starts_at' => '2026-01-01',
                    'due_at' => '2026-01-07',
                ],
            ],
        ];

        $response = $this->actingAs($supervisor)->post(route('goals.store'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('goals', ['title' => 'Save Key Action Steps Array']);

        $goal = Goal::where('title', 'Save Key Action Steps Array')->first();

        $this->assertNotNull($goal, 'Goal was not created');
        $this->assertIsArray($goal->key_action_steps);
        $this->assertSame(['Plan work', 'Execute objectives', 'Report weekly'], $goal->key_action_steps);
    }
}
