<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Goal;
use App\Models\GoalPillar;
use App\Models\Quarter;
use App\Models\User;
use App\Notifications\WorkflowNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WorkflowNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_submission_notifies_a_reviewer_and_review_notifies_the_author(): void
    {
        [$staff, $supervisor, $objective] = $this->goalFixture();

        $this->actingAs($staff)->post(route('objectives.weekly-updates.store', $objective), [
            'report_date' => '2026-01-01',
            'reporting_frequency' => 'weekly',
            'achievement_summary' => 'Completed the first reporting period.',
        ])->assertRedirect();

        $update = $objective->weeklyUpdates()->latest('id')->firstOrFail();
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => $supervisor->getMorphClass(),
            'notifiable_id' => $supervisor->id,
        ]);
        $this->assertSame('New report awaiting review', $supervisor->notifications()->latest()->first()->data['title']);

        $this->actingAs($supervisor)->post(route('weekly-updates.reviews.store', $update), [
            'decision' => 'rejected',
            'comments' => 'Please include the supporting evidence.',
        ])->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => $staff->getMorphClass(),
            'notifiable_id' => $staff->id,
        ]);
        $this->assertSame('Report rejected', $staff->notifications()->latest()->first()->data['title']);
    }

    public function test_overdue_report_notifications_are_idempotent(): void
    {
        [$staff, $supervisor, $objective] = $this->goalFixture();

        $this->artisan('notifications:overdue-reports', ['--date' => '2026-01-10'])
            ->assertSuccessful();
        $this->artisan('notifications:overdue-reports', ['--date' => '2026-01-10'])
            ->assertSuccessful();

        $this->assertDatabaseCount('reporting_reminders', 2);
        $this->assertSame(1, $staff->notifications()->whereJsonContains('data->context->event', 'report_overdue')->count());
        $this->assertSame(1, $supervisor->notifications()->whereJsonContains('data->context->event', 'report_overdue')->count());
    }

    public function test_notification_history_can_mark_only_the_current_users_notification_as_read(): void
    {
        [$staff, , $objective] = $this->goalFixture();
        $staff->notify(new WorkflowNotification(
            'Test notification',
            'Open this goal.',
            route('goals.show', $objective->goal),
        ));
        $notification = $staff->notifications()->firstOrFail();

        $this->actingAs($staff)->get(route('notifications.read', $notification))
            ->assertRedirect(route('goals.show', $objective->goal));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    private function goalFixture(): array
    {
        Permission::findOrCreate('submit daily reports');
        Role::findOrCreate('Staff')->syncPermissions(['submit daily reports']);

        $department = Department::create(['name' => 'Programs Department']);
        $section = $department->sections()->create(['name' => 'Programs Section']);
        $quarter = Quarter::create([
            'name' => 'Q1 2026',
            'starts_at' => '2026-01-01',
            'ends_at' => '2026-03-31',
        ]);
        $pillar = GoalPillar::create([
            'name' => 'Operational Excellence',
            'annual_goal' => 'Improve delivery quality.',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $staff = User::factory()->create([
            'department_id' => $department->id,
            'section_id' => $section->id,
            'role' => 'staff',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        $staff->assignRole('Staff');
        $supervisor = User::factory()->create([
            'department_id' => $department->id,
            'section_id' => $section->id,
            'role' => 'supervisor',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $goal = Goal::create([
            'quarter_id' => $quarter->id,
            'goal_pillar_id' => $pillar->id,
            'created_by' => $staff->id,
            'owner_id' => $staff->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
        $goal->assignments()->create([
            'department_id' => $department->id,
            'section_id' => $section->id,
        ]);
        $objective = $goal->objectives()->create([
            'title' => 'Improve reporting quality',
            'key_activities' => 'Review and submit evidence.',
            'specific_output' => 'Approved progress report.',
            'weight' => 100,
            'planned_weeks' => 3,
            'reporting_frequency' => ['weekly'],
            'starts_at' => '2026-01-01',
            'due_at' => '2026-01-21',
        ]);

        return [$staff, $supervisor, $objective];
    }
}
