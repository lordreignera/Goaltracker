<?php

namespace App\Console\Commands;

use App\Models\GoalObjective;
use App\Models\User;
use App\Notifications\WorkflowNotification;
use App\Services\AuditLogger;
use App\Services\GoalAccessService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendOverdueReportNotifications extends Command
{
    protected $signature = 'notifications:overdue-reports {--date= : Evaluate overdue reports as of this date}';

    protected $description = 'Notify responsible users about missing reports for completed reporting periods';

    public function handle(GoalAccessService $access, AuditLogger $audit): int
    {
        if (! config('workflow.overdue_reminders_enabled')) {
            $this->warn('Overdue report reminders are disabled.');

            return self::SUCCESS;
        }

        $today = CarbonImmutable::parse($this->option('date') ?: 'today')->startOfDay();
        $users = User::query()
            ->with(['roles', 'permissions', 'accessibleDepartments'])
            ->where('approval_status', 'approved')
            ->where('is_active', true)
            ->get();
        $sent = 0;

        GoalObjective::query()
            ->whereNotIn('status', ['completed'])
            ->whereHas('goal', fn ($query) => $query->whereIn('status', ['submitted', 'approved', 'in_progress']))
            ->with(['goal.quarter', 'goal.assignments', 'weeklyUpdates'])
            ->chunkById(100, function ($objectives) use ($access, $today, $users, &$sent) {
                foreach ($objectives as $objective) {
                    foreach ($objective->reportingFrequencies() as $frequency) {
                        $period = $this->mostRecentCompletedPeriod($objective, $frequency, $today);
                        if (! $period) {
                            continue;
                        }

                        [$periodStart, $periodEnd] = $period;
                        foreach ($users as $user) {
                            if (! $this->isResponsibleUser($user, $objective, $access)) {
                                continue;
                            }

                            $hasReport = $objective->weeklyUpdates->contains(fn ($update) => $update->user_id === $user->id
                                && $update->reporting_frequency === $frequency
                                && $update->report_period_start?->isSameDay($periodStart)
                            );
                            if ($hasReport) {
                                continue;
                            }

                            $inserted = DB::table('reporting_reminders')->insertOrIgnore([
                                'goal_objective_id' => $objective->id,
                                'user_id' => $user->id,
                                'reporting_frequency' => $frequency,
                                'period_start' => $periodStart->toDateString(),
                                'period_end' => $periodEnd->toDateString(),
                                'notified_at' => now(),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            if ($inserted === 0) {
                                continue;
                            }

                            $user->notify(new WorkflowNotification(
                                title: 'Reporting update overdue',
                                message: sprintf(
                                    'Your %s report for %s was due on %s.',
                                    $frequency,
                                    $objective->title,
                                    $periodEnd->format('M j, Y')
                                ),
                                url: route('goals.show', $objective->goal).'#report-objective-'.$objective->id,
                                level: 'warning',
                                context: [
                                    'goal_id' => $objective->goal_id,
                                    'goal_objective_id' => $objective->id,
                                    'event' => 'report_overdue',
                                    'frequency' => $frequency,
                                    'period_start' => $periodStart->toDateString(),
                                    'period_end' => $periodEnd->toDateString(),
                                ],
                            ));
                            $sent++;
                        }
                    }
                }
            });

        $audit->record('reporting', 'overdue_notifications.sent', 'Evaluated overdue reporting notifications.', metadata: [
            'evaluation_date' => $today->toDateString(),
            'notifications_sent' => $sent,
        ]);
        $this->info("Sent {$sent} overdue reporting notification(s).");

        return self::SUCCESS;
    }

    private function mostRecentCompletedPeriod(
        GoalObjective $objective,
        string $frequency,
        CarbonImmutable $today
    ): ?array {
        [$rangeStart, $rangeEnd] = $objective->reportingDateRange();
        $rangeStart = CarbonImmutable::instance($rangeStart);
        $rangeEnd = CarbonImmutable::instance($rangeEnd);

        if ($today->lte($rangeStart)) {
            return null;
        }

        $reference = $today->subDay()->min($rangeEnd);
        [$periodStart, $periodEnd] = $objective->reportingPeriodFor($reference, $frequency);

        if ($periodEnd->gte($today)) {
            $reference = CarbonImmutable::instance($periodStart)->subDay();
            if ($reference->lt($rangeStart)) {
                return null;
            }
            [$periodStart, $periodEnd] = $objective->reportingPeriodFor($reference, $frequency);
        }

        return $periodEnd->lt($today) ? [$periodStart, $periodEnd] : null;
    }

    private function isResponsibleUser(User $user, GoalObjective $objective, GoalAccessService $access): bool
    {
        $goal = $objective->goal;
        $explicitlyResponsible = in_array($user->id, [$goal->owner_id, $goal->created_by], true)
            || $goal->assignments->contains('user_id', $user->id);

        if ($user->isAdmin() && ! $explicitlyResponsible) {
            return false;
        }

        return $access->canSubmitDailyReport($user, $goal);
    }
}
