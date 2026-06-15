<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Goal;
use App\Models\Quarter;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeeklyUpdateFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_cannot_repeat_weekly_report_date_for_same_objective(): void
    {
        [$staff, $objective] = $this->staffAndObjective();

        $payload = [
            'week_number' => 1,
            'week_starting' => '2026-01-01',
            'progress_summary' => 'Installed first batch.',
            'achievements' => ['Installed computers'],
            'challenges' => ['Procurement delay'],
            'next_actions' => ['Complete remaining installations'],
        ];

        $this->actingAs($staff)->post(route('objectives.weekly-updates.store', $objective), $payload)
            ->assertRedirect();

        $this->actingAs($staff)->post(route('objectives.weekly-updates.store', $objective), $payload)
            ->assertSessionHasErrors('week_starting');

        $this->assertSame(1, $objective->weeklyUpdates()->count());
    }

    public function test_staff_can_edit_unapproved_weekly_submission(): void
    {
        [$staff, $objective] = $this->staffAndObjective();

        $update = $objective->weeklyUpdates()->create([
            'user_id' => $staff->id,
            'week_number' => 1,
            'week_starting' => '2026-01-01',
            'progress_summary' => 'Original report.',
            'achievements' => "Original achievement",
            'challenges' => "Original challenge",
            'next_actions' => "Original action",
            'status' => 'submitted',
        ]);

        $this->actingAs($staff)->put(route('weekly-updates.update', $update), [
            'week_number' => 2,
            'week_starting' => '2026-01-08',
            'progress_summary' => 'Updated report.',
            'achievements' => ['Installed Kampala office computers', 'Configured user accounts'],
            'challenges' => ['Delayed procurement'],
            'next_actions' => ['Complete remaining installations'],
        ])->assertRedirect();

        $update->refresh();

        $this->assertSame(2, $update->week_number);
        $this->assertSame('2026-01-08', $update->week_starting->toDateString());
        $this->assertSame('Updated report.', $update->progress_summary);
        $this->assertSame("Installed Kampala office computers\nConfigured user accounts", $update->achievements);
        $this->assertSame('submitted', $update->status);
    }

    public function test_approved_weekly_submission_cannot_be_edited_by_staff(): void
    {
        [$staff, $objective] = $this->staffAndObjective();

        $update = $objective->weeklyUpdates()->create([
            'user_id' => $staff->id,
            'week_number' => 1,
            'week_starting' => '2026-01-01',
            'progress_summary' => 'Approved report.',
            'status' => 'approved',
        ]);

        $this->actingAs($staff)->put(route('weekly-updates.update', $update), [
            'week_number' => 2,
            'week_starting' => '2026-01-08',
            'progress_summary' => 'Trying to change approved report.',
        ])->assertForbidden();

        $this->assertSame('Approved report.', $update->fresh()->progress_summary);
    }

    public function test_goal_progress_requires_an_approved_weekly_report(): void
    {
        [$staff, $objective] = $this->staffAndObjective();
        $goal = $objective->goal;

        $objective->update(['status' => 'completed']);

        $objective->weeklyUpdates()->create([
            'user_id' => $staff->id,
            'week_number' => 1,
            'week_starting' => '2026-01-01',
            'progress_summary' => 'Submitted but not reviewed.',
            'status' => 'submitted',
        ]);

        $this->assertSame(0, $goal->fresh()->progress());

        $objective->weeklyUpdates()->first()->update(['status' => 'approved']);

        $this->assertSame(33, $goal->fresh()->progress());
    }

    private function staffAndObjective(): array
    {
        $department = Department::create(['name' => 'ICT Department']);
        $unit = Unit::create(['department_id' => $department->id, 'name' => 'Software Development Unit']);
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);

        $staff = User::factory()->create([
            'department_id' => $department->id,
            'unit_id' => $unit->id,
            'role' => 'staff',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $goal = Goal::create([
            'quarter_id' => $quarter->id,
            'owner_id' => $staff->id,
            'title' => 'Improve ICT Service Delivery',
            'level' => 'unit',
        ]);
        $goal->assignments()->create(['department_id' => $department->id, 'unit_id' => $unit->id]);

        $objective = $goal->objectives()->create([
            'title' => 'Upgrade computers',
            'specific_output' => 'Upgrade staff computers.',
            'success_measure' => 'Computers are functional and signed off.',
            'weight' => 100,
            'planned_weeks' => 3,
            'starts_at' => '2026-01-01',
            'due_at' => '2026-01-21',
        ]);

        return [$staff, $objective];
    }
}
