<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\Quarter;
use App\Models\User;
use Illuminate\Support\Collection;

class QuarterlyReportService
{
    public function build(Quarter $quarter, User $user): array
    {
        $goals = Goal::query()
            ->visibleTo($user)
            ->where('quarter_id', $quarter->id)
            ->with([
                'assignedDepartments',
                'assignedSections',
                'assignedUnits',
                'objectives.weeklyUpdates.user',
                'objectives.weeklyUpdates.reviews.supervisor',
            ])
            ->orderBy('title')
            ->get();

        $goalRows = $goals->map(fn (Goal $goal) => $this->goalRow($goal));
        $departmentRows = $this->departmentRows($goals);
        $weeklyRows = $this->weeklyRows($goals);

        return [
            'quarter' => $quarter,
            'goals' => $goals,
            'goalRows' => $goalRows,
            'departmentRows' => $departmentRows,
            'weeklyRows' => $weeklyRows,
            'summary' => [
                'goals_planned' => $goals->count(),
                'goals_achieved' => $goalRows->where('progress', '>=', 100)->count(),
                'average_progress' => round($goalRows->avg('progress') ?? 0, 1),
                'approved_weekly_reports' => $weeklyRows->where('status', 'approved')->count(),
                'submitted_weekly_reports' => $weeklyRows->count(),
                'pending_reviews' => $weeklyRows->where('status', 'submitted')->count(),
            ],
        ];
    }

    private function goalRow(Goal $goal): array
    {
        return [
            'title' => $goal->title,
            'department' => $goal->assignedDepartments->pluck('name')->unique()->join(', '),
            'section' => $goal->assignedSections->pluck('name')->unique()->join(', ') ?: 'Department-wide',
            'unit' => $goal->assignedUnits->pluck('name')->unique()->join(', ') ?: 'All units',
            'progress' => $goal->progress(),
            'objectives_count' => $goal->objectives->count(),
            'approved_weeks' => $goal->objectives->sum(fn ($objective) => $objective->approvedReportingWeeksCount()),
            'planned_weeks' => $goal->objectives->sum(fn ($objective) => $objective->totalReportingWeeks()),
        ];
    }

    private function departmentRows(Collection $goals): Collection
    {
        return $goals
            ->flatMap(function (Goal $goal) {
                $departments = $goal->assignedDepartments;

                return $departments->map(fn ($department) => [
                    'department' => $department->name,
                    'goal' => $goal,
                    'progress' => $goal->progress(),
                ]);
            })
            ->groupBy('department')
            ->map(fn (Collection $rows, string $department) => [
                'department' => $department,
                'goals_count' => $rows->count(),
                'progress' => round($rows->avg('progress') ?? 0, 1),
            ])
            ->values()
            ->sortBy('department')
            ->values();
    }

    private function weeklyRows(Collection $goals): Collection
    {
        return $goals->flatMap(function (Goal $goal) {
            return $goal->objectives->flatMap(function ($objective) use ($goal) {
                return $objective->weeklyUpdates->map(function ($update) use ($goal, $objective) {
                    return [
                        'goal' => $goal->title,
                        'objective' => $objective->title,
                        'objective_specific_output' => $objective->specific_output,
                        'objective_success_measure' => $objective->success_measure,
                        'objective_planned_weeks' => $objective->planned_weeks,
                        'week_number' => $update->week_number,
                        'week_starting' => $update->week_starting,
                        'staff' => $update->user?->name,
                        'summary' => $update->progress_summary,
                        'achievements' => $this->lines($update->achievements),
                        'challenges' => $this->lines($update->challenges),
                        'recommendations' => $this->lines($update->next_actions),
                        'status' => $update->status,
                        'review_comments' => $update->reviews->pluck('comments')->filter()->join("\n"),
                    ];
                });
            });
        })->sortBy([
            ['goal', 'asc'],
            ['objective', 'asc'],
            ['week_number', 'asc'],
        ])->values();
    }

    private function lines(?string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $value, -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
