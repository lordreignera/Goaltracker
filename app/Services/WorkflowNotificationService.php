<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\User;
use App\Models\WeeklyUpdate;
use App\Notifications\WorkflowNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class WorkflowNotificationService
{
    public function __construct(private readonly GoalAccessService $access) {}

    public function goalSubmitted(Goal $goal, User $submittedBy): void
    {
        $goal->loadMissing('pillar');
        $this->sendToReviewers($goal, $submittedBy, new WorkflowNotification(
            title: 'Work plan submitted for review',
            message: "{$submittedBy->name} submitted a work plan under {$goal->pillar?->name}.",
            url: route('goals.show', $goal),
            context: ['goal_id' => $goal->id, 'event' => 'goal_submitted'],
        ));
    }

    public function reportSubmitted(WeeklyUpdate $update, User $submittedBy, bool $resubmitted = false): void
    {
        $update->loadMissing('objective.goal.pillar');
        $goal = $update->objective->goal;
        $verb = $resubmitted ? 'resubmitted' : 'submitted';

        $this->sendToReviewers($goal, $submittedBy, new WorkflowNotification(
            title: $resubmitted ? 'Report resubmitted for review' : 'New report awaiting review',
            message: "{$submittedBy->name} {$verb} a {$update->reporting_frequency} report for {$update->objective->title}.",
            url: route('goals.show', $goal).'#report-'.$update->id,
            context: ['goal_id' => $goal->id, 'weekly_update_id' => $update->id, 'event' => 'report_submitted'],
        ));
    }

    public function reportReviewed(WeeklyUpdate $update, User $reviewedBy, string $decision): void
    {
        $update->loadMissing(['user', 'objective.goal']);
        $labels = [
            'approved' => ['Report approved', 'approved', 'success'],
            'rejected' => ['Report rejected', 'rejected', 'danger'],
            'revision_requested' => ['Report revision requested', 'requested changes to', 'warning'],
        ];
        [$title, $verb, $level] = $labels[$decision];

        $update->user->notify(new WorkflowNotification(
            title: $title,
            message: "{$reviewedBy->name} {$verb} your {$update->reporting_frequency} report for {$update->objective->title}.",
            url: route('goals.show', $update->objective->goal).'#report-'.$update->id,
            level: $level,
            context: ['goal_id' => $update->objective->goal->id, 'weekly_update_id' => $update->id, 'event' => 'report_reviewed', 'decision' => $decision],
        ));
    }

    public function reviewersFor(Goal $goal): Collection
    {
        return User::query()
            ->where('approval_status', 'approved')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereIn('role', ['admin', 'supervisor', 'manager'])
                    ->orWhereHas('roles', fn ($query) => $query->whereIn('name', ['Super Admin', 'Admin', 'Supervisor', 'Manager']))
                    ->orWhereHas('permissions', fn ($query) => $query->where('name', 'review goals'));
            })
            ->get()
            ->filter(fn (User $user) => $this->access->canReviewGoal($user, $goal))
            ->values();
    }

    private function sendToReviewers(Goal $goal, User $actor, WorkflowNotification $notification): void
    {
        $reviewers = $this->reviewersFor($goal)->reject(fn (User $user) => $user->is($actor));
        Notification::send($reviewers, $notification);
    }
}
