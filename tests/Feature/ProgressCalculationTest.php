<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Goal;
use App\Models\Quarter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgressCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_goal_progress_uses_weighted_supervisor_verified_scores(): void
    {
        $department = Department::create(['name' => 'Community Outreach']);
        $quarter = Quarter::create(['name' => 'Q3 2026', 'starts_at' => '2026-07-01', 'ends_at' => '2026-09-30']);
        $supervisor = User::factory()->create(['role' => 'admin']);

        $goal = Goal::create([
            'quarter_id' => $quarter->id,
            'title' => 'Increase effective community cells',
        ]);
        $goal->assignments()->create(['department_id' => $department->id]);

        $this->addVerifiedObjective($goal, $supervisor, 'Train cell leaders', 30, 60);
        $this->addVerifiedObjective($goal, $supervisor, 'Launch new community cells', 30, 80);
        $this->addVerifiedObjective($goal, $supervisor, 'Confirm cell effectiveness', 40, 50);

        $this->assertSame(62, $goal->fresh()->progress());
    }

    public function test_dashboard_organization_score_averages_department_scores(): void
    {
        $quarter = Quarter::create(['name' => 'Q3 2026', 'starts_at' => '2026-07-01', 'ends_at' => '2026-09-30']);
        $admin = User::factory()->create(['role' => 'admin']);

        $childcare = Department::create(['name' => 'Childcare']);
        $outreach = Department::create(['name' => 'Community Outreach']);
        $finance = Department::create(['name' => 'Finance']);

        $this->createGoalWithProgress($quarter, $childcare, $admin, 'Childcare A', 62);
        $this->createGoalWithProgress($quarter, $childcare, $admin, 'Childcare B', 78);
        $this->createGoalWithProgress($quarter, $outreach, $admin, 'Outreach A', 50);
        $this->createGoalWithProgress($quarter, $outreach, $admin, 'Outreach B', 64);
        $this->createGoalWithProgress($quarter, $finance, $admin, 'Finance A', 80);
        $this->createGoalWithProgress($quarter, $finance, $admin, 'Finance B', 90);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Organization Score')
            ->assertSee('70.7%');
    }

    private function createGoalWithProgress(Quarter $quarter, Department $department, User $supervisor, string $title, int $score): Goal
    {
        $goal = Goal::create([
            'quarter_id' => $quarter->id,
            'title' => $title,
        ]);
        $goal->assignments()->create(['department_id' => $department->id]);
        $this->addVerifiedObjective($goal, $supervisor, $title.' objective', 100, $score);

        return $goal;
    }

    private function addVerifiedObjective(Goal $goal, User $supervisor, string $title, int $weight, int $verifiedScore): void
    {
        $objective = $goal->objectives()->create([
            'title' => $title,
            'specific_output' => $title.' deliverable',
            'weight' => $weight,
            'planned_weeks' => 4,
            'starts_at' => '2026-07-01',
            'due_at' => '2026-07-28',
        ]);

        $update = $objective->weeklyUpdates()->create([
            'user_id' => $supervisor->id,
            'report_date' => '2026-07-15',
            'achievement_percentage' => 100,
            'achievement_summary' => 'Staff claim for calculation test.',
            'status' => 'approved',
        ]);

        $update->reviews()->create([
            'supervisor_id' => $supervisor->id,
            'decision' => 'approved',
            'verified_percentage' => $verifiedScore,
            'comments' => 'Verified score.',
        ]);
    }
}
