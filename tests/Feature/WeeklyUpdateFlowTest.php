<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Goal;
use App\Models\Quarter;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WeeklyUpdateFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_cannot_repeat_daily_report_date_for_same_objective(): void
    {
        [$staff, $objective] = $this->staffAndObjective();

        $payload = [
            'report_date' => '2026-01-01',
            'reporting_frequency' => 'daily',
            'achievement_percentage' => 25,
            'achievement_summary' => 'Installed first batch.',
            'challenges' => 'Procurement delay remains.',
            'action_points' => 'Follow up with procurement.',
        ];

        $this->actingAs($staff)->post(route('objectives.weekly-updates.store', $objective), $payload)
            ->assertRedirect();

        $this->actingAs($staff)->post(route('objectives.weekly-updates.store', $objective), $payload)
            ->assertSessionHasErrors('report_date');

        $this->assertSame(1, $objective->weeklyUpdates()->count());
    }

    public function test_daily_and_weekly_reports_can_coexist_for_same_objective_and_date(): void
    {
        [$staff, $objective] = $this->staffAndObjective();

        $this->actingAs($staff)->post(route('objectives.weekly-updates.store', $objective), [
            'report_date' => '2026-01-01',
            'reporting_frequency' => 'daily',
            'achievement_summary' => 'Daily implementation note.',
        ])->assertRedirect();

        $this->actingAs($staff)->post(route('objectives.weekly-updates.store', $objective), [
            'report_date' => '2026-01-01',
            'reporting_frequency' => 'weekly',
            'achievement_summary' => 'Weekly summary note.',
        ])->assertRedirect();

        $this->assertSame(2, $objective->weeklyUpdates()->count());
    }

    public function test_weekly_reporting_allows_only_one_report_per_week_period(): void
    {
        [$staff, $objective] = $this->staffAndObjective();

        $this->actingAs($staff)->post(route('objectives.weekly-updates.store', $objective), [
            'report_date' => '2026-01-01',
            'reporting_frequency' => 'weekly',
            'achievement_summary' => 'First report in the first week.',
        ])->assertRedirect();

        $this->actingAs($staff)->post(route('objectives.weekly-updates.store', $objective), [
            'report_date' => '2026-01-02',
            'reporting_frequency' => 'weekly',
            'achievement_summary' => 'Second report in the same week.',
        ])->assertSessionHasErrors('report_date');

        $this->actingAs($staff)->post(route('objectives.weekly-updates.store', $objective), [
            'report_date' => '2026-01-08',
            'reporting_frequency' => 'weekly',
            'achievement_summary' => 'First report in the second week.',
        ])->assertRedirect();

        $this->assertSame(2, $objective->weeklyUpdates()->count());
    }

    public function test_goal_report_ui_shows_regulated_reporting_fields(): void
    {
        [$staff, $objective] = $this->staffAndObjective();

        $response = $this->actingAs($staff)->get(route('goals.show', $objective->goal));

        $response->assertOk()
            ->assertSee('Weekly reporting')
            ->assertSee('This report updates progress')
            ->assertSee('Required only when updating progress')
            ->assertSee('Submit Report')
            ->assertSee('Reporting Period')
            ->assertSee('Progress Update')
            ->assertSee('data-progress-score disabled', false)
            ->assertDontSee('Submit Daily Report');
    }

    public function test_normal_report_does_not_store_achievement_score(): void
    {
        [$staff, $objective] = $this->staffAndObjective();

        $this->actingAs($staff)->post(route('objectives.weekly-updates.store', $objective), [
            'report_date' => '2026-01-01',
            'achievement_percentage' => 25,
            'achievement_summary' => 'Shared an activity note without updating official progress.',
        ])->assertRedirect();

        $update = $objective->weeklyUpdates()->firstOrFail();

        $this->assertFalse($update->is_progress_update);
        $this->assertNull($update->achievement_percentage);
        $this->assertSame(0, $objective->fresh()->progressPercent());
    }

    public function test_progress_update_requires_achievement_score(): void
    {
        [$staff, $objective] = $this->staffAndObjective();

        $this->actingAs($staff)->post(route('objectives.weekly-updates.store', $objective), [
            'report_date' => '2026-01-01',
            'is_progress_update' => 1,
            'achievement_summary' => 'This should be scored but no score was supplied.',
        ])->assertSessionHasErrors('achievement_percentage');

        $this->assertSame(0, $objective->weeklyUpdates()->count());
    }

    public function test_staff_can_edit_unapproved_daily_report(): void
    {
        [$staff, $objective] = $this->staffAndObjective();

        $update = $objective->weeklyUpdates()->create([
            'user_id' => $staff->id,
            'report_date' => '2026-01-01',
            'achievement_percentage' => 25,
            'achievement_summary' => 'Original report.',
            'status' => 'submitted',
        ]);

        $this->actingAs($staff)->put(route('weekly-updates.update', $update), [
            'report_date' => '2026-01-08',
            'is_progress_update' => 1,
            'achievement_percentage' => 50,
            'achievement_summary' => 'Updated report with configured user accounts.',
            'challenges' => 'No blockers.',
            'action_points' => 'Confirm usage.',
        ])->assertRedirect();

        $update->refresh();

        $this->assertSame('2026-01-08', $update->report_date->toDateString());
        $this->assertSame(50, $update->achievement_percentage);
        $this->assertSame('Updated report with configured user accounts.', $update->achievement_summary);
        $this->assertSame('submitted', $update->status);
    }

    public function test_user_without_daily_report_permission_cannot_submit_report(): void
    {
        [$staff, $objective] = $this->staffAndObjective();
        $staff->syncRoles([]);

        $this->actingAs($staff)->post(route('objectives.weekly-updates.store', $objective), [
            'report_date' => '2026-01-01',
            'achievement_percentage' => 25,
            'achievement_summary' => 'Trying without the submit permission.',
        ])->assertForbidden();

        $this->assertSame(0, $objective->weeklyUpdates()->count());
    }

    public function test_user_without_daily_report_permission_cannot_edit_report(): void
    {
        [$staff, $objective] = $this->staffAndObjective();

        $update = $objective->weeklyUpdates()->create([
            'user_id' => $staff->id,
            'report_date' => '2026-01-01',
            'achievement_percentage' => 25,
            'achievement_summary' => 'Original report.',
            'status' => 'submitted',
        ]);

        $staff->syncRoles([]);

        $this->actingAs($staff)->put(route('weekly-updates.update', $update), [
            'report_date' => '2026-01-08',
            'achievement_percentage' => 50,
            'achievement_summary' => 'Trying without the submit permission.',
        ])->assertForbidden();

        $this->assertSame('Original report.', $update->fresh()->achievement_summary);
    }

    public function test_manager_can_submit_daily_report_through_goal_management_permission(): void
    {
        [$manager, $objective] = $this->staffAndObjective('Manager', 'manager');

        $this->actingAs($manager)->post(route('objectives.weekly-updates.store', $objective), [
            'report_date' => '2026-01-01',
            'achievement_percentage' => 25,
            'achievement_summary' => 'Manager submitted report.',
        ])->assertRedirect();

        $this->assertSame(1, $objective->weeklyUpdates()->count());
    }

    public function test_staff_can_attach_evidence_to_daily_report(): void
    {
        Storage::fake('public');

        [$staff, $objective] = $this->staffAndObjective();

        $this->actingAs($staff)->post(route('objectives.weekly-updates.store', $objective), [
            'report_date' => '2026-01-01',
            'achievement_percentage' => 25,
            'achievement_summary' => 'Completed first training session.',
            'challenges' => 'Two leaders were absent.',
            'action_points' => 'Schedule catch-up training.',
            'evidence_file' => UploadedFile::fake()->image('training-register.jpg', 120, 80),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $update = $objective->weeklyUpdates()->firstOrFail();

        Storage::disk('public')->assertExists($update->evidence_path);
        $this->assertSame('training-register.jpg', $update->evidence_original_name);

        $this->actingAs($staff)->get(route('weekly-updates.evidence', $update))
            ->assertOk()
            ->assertDownload('training-register.jpg');
    }

    public function test_approved_daily_report_cannot_be_edited_by_staff(): void
    {
        [$staff, $objective] = $this->staffAndObjective();

        $update = $objective->weeklyUpdates()->create([
            'user_id' => $staff->id,
            'report_date' => '2026-01-01',
            'achievement_percentage' => 75,
            'achievement_summary' => 'Approved report.',
            'status' => 'approved',
        ]);

        $this->actingAs($staff)->put(route('weekly-updates.update', $update), [
            'report_date' => '2026-01-08',
            'achievement_percentage' => 90,
            'achievement_summary' => 'Trying to change approved report.',
        ])->assertForbidden();

        $this->assertSame('Approved report.', $update->fresh()->achievement_summary);
    }

    public function test_goal_progress_requires_an_approved_daily_report(): void
    {
        [$staff, $objective] = $this->staffAndObjective();
        $goal = $objective->goal;

        $objective->update(['status' => 'completed']);

        $update = $objective->weeklyUpdates()->create([
            'user_id' => $staff->id,
            'report_date' => '2026-01-01',
            'achievement_percentage' => 80,
            'achievement_summary' => 'Submitted but not reviewed.',
            'status' => 'submitted',
        ]);

        $this->assertSame(0, $goal->fresh()->progress());

        $update->update(['status' => 'approved']);
        $update->reviews()->create([
            'supervisor_id' => $staff->id,
            'decision' => 'approved',
            'verified_percentage' => 33,
            'comments' => 'Verified against actual completion.',
        ]);

        $this->assertSame(33, $goal->fresh()->progress());
    }

    public function test_supervisor_verified_score_is_used_as_source_of_truth(): void
    {
        [$staff, $objective] = $this->staffAndObjective();
        $supervisor = User::factory()->create([
            'department_id' => $staff->department_id,
            'unit_id' => $staff->unit_id,
            'role' => 'supervisor',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        Permission::findOrCreate('review goals');
        Role::findOrCreate('Supervisor')->syncPermissions(['review goals']);
        $supervisor->assignRole('Supervisor');

        $update = $objective->weeklyUpdates()->create([
            'user_id' => $staff->id,
            'report_date' => '2026-01-01',
            'achievement_percentage' => 80,
            'achievement_summary' => 'Staff reported 80 percent.',
            'status' => 'submitted',
        ]);

        $this->actingAs($supervisor)->post(route('weekly-updates.reviews.store', $update), [
            'decision' => 'approved',
            'verified_percentage' => 60,
            'comments' => 'Supervisor verified 60 percent.',
        ])->assertRedirect();

        $this->assertSame('approved', $update->fresh()->status);
        $this->assertSame(60, $objective->fresh()->approvedAchievementPercent());
        $this->assertSame(60, $objective->goal->fresh()->progress());
    }

    public function test_approved_review_requires_verified_score(): void
    {
        [$staff, $objective] = $this->staffAndObjective();
        $supervisor = User::factory()->create([
            'department_id' => $staff->department_id,
            'unit_id' => $staff->unit_id,
            'role' => 'supervisor',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        Permission::findOrCreate('review goals');
        Role::findOrCreate('Supervisor')->syncPermissions(['review goals']);
        $supervisor->assignRole('Supervisor');

        $update = $objective->weeklyUpdates()->create([
            'user_id' => $staff->id,
            'report_date' => '2026-01-01',
            'achievement_percentage' => 80,
            'achievement_summary' => 'Staff reported 80 percent.',
            'status' => 'submitted',
        ]);

        $this->actingAs($supervisor)->post(route('weekly-updates.reviews.store', $update), [
            'decision' => 'approved',
            'comments' => 'Missing verified score.',
        ])->assertSessionHasErrors('verified_percentage');

        $this->assertSame('submitted', $update->fresh()->status);
    }

    private function staffAndObjective(string $roleName = 'Staff', string $userRole = 'staff'): array
    {
        Permission::findOrCreate('submit daily reports');
        Permission::findOrCreate('manage goals');

        Role::findOrCreate('Staff')->syncPermissions(['submit daily reports']);
        Role::findOrCreate('Manager')->syncPermissions(['manage goals']);

        $department = Department::create(['name' => 'ICT Department']);
        $unit = Unit::create(['department_id' => $department->id, 'name' => 'Software Development Unit']);
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);

        $staff = User::factory()->create([
            'department_id' => $department->id,
            'unit_id' => $unit->id,
            'role' => $userRole,
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        $staff->assignRole($roleName);

        $goal = Goal::create([
            'quarter_id' => $quarter->id,
            'owner_id' => $staff->id,
            'title' => 'Improve ICT Service Delivery',
            'level' => 'unit',
        ]);
        $goal->assignments()->create(['department_id' => $department->id, 'unit_id' => $unit->id]);

        $objective = $goal->objectives()->create([
            'title' => 'Upgrade computers',
            'key_activities' => 'Procure, configure, and install replacement computers.',
            'specific_output' => 'Upgrade staff computers and confirm they are functional.',
            'weight' => 100,
            'planned_weeks' => 3,
            'reporting_frequency' => ['daily', 'weekly'],
            'starts_at' => '2026-01-01',
            'due_at' => '2026-01-21',
        ]);

        return [$staff, $objective];
    }
}
