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
                'pillar',
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
        $reportRows = $this->reportRows($goals);

        return [
            'quarter' => $quarter,
            'goals' => $goals,
            'goalRows' => $goalRows,
            'departmentRows' => $departmentRows,
            'reportRows' => $reportRows,
            'summary' => [
                'goals_planned' => $goals->count(),
                'goals_achieved' => $goalRows->where('progress', '>=', 100)->count(),
                'average_progress' => round($goalRows->avg('progress') ?? 0, 1),
                'approved_daily_reports' => $reportRows->where('status', 'approved')->count(),
                'submitted_daily_reports' => $reportRows->count(),
                'pending_reviews' => $reportRows->where('status', 'submitted')->count(),
            ],
        ];
    }

    private function goalRow(Goal $goal): array
    {
        return [
            'title' => $goal->title,
            'pillar' => $goal->pillar?->name ?? 'Unassigned',
            'department' => $goal->assignedDepartments->pluck('name')->unique()->join(', '),
            'section' => $goal->assignedSections->pluck('name')->unique()->join(', ') ?: 'Department-wide',
            'unit' => $goal->assignedUnits->pluck('name')->unique()->join(', ') ?: 'All units',
            'progress' => $goal->progress(),
            'objectives_count' => $goal->objectives->count(),
            'achievement' => round($goal->objectives->avg(fn ($objective) => $objective->progressPercent()) ?? 0, 1),
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

    private function reportRows(Collection $goals): Collection
    {
        return $goals->flatMap(function (Goal $goal) {
            return $goal->objectives->flatMap(function ($objective) use ($goal) {
                return $objective->weeklyUpdates->map(function ($update) use ($goal, $objective) {
                    return [
                        'goal' => $goal->title,
                        'pillar' => $goal->pillar?->name ?? 'Unassigned',
                        'objective' => $objective->title,
                        'objective_key_activities' => $objective->key_activities,
                        'objective_specific_output' => $objective->specific_output,
                        'objective_planned_weeks' => $objective->planned_weeks,
                        'reporting_frequency' => $objective->reporting_frequency,
                        'timeline' => $objective->starts_at?->format('M d, Y').' - '.$objective->due_at?->format('M d, Y'),
                        'report_date' => $update->report_date,
                        'report_period' => $update->report_period_start?->format('M d, Y').' - '.$update->report_period_end?->format('M d, Y'),
                        'staff' => $update->user?->name,
                        'is_progress_update' => $update->is_progress_update,
                        'achievement_percentage' => $update->achievement_percentage,
                        'verified_percentage' => $update->verifiedAchievementPercent(),
                        'achievement_summary' => $update->achievement_summary,
                        'challenges' => $update->challenges,
                        'action_points' => $update->action_points,
                        'evidence_name' => $update->evidence_original_name,
                        'has_evidence' => $update->hasEvidence(),
                        'status' => $update->status,
                        'review_comments' => $update->reviews->pluck('comments')->filter()->join("\n"),
                    ];
                });
            });
        })->sortBy([
            ['pillar', 'asc'],
            ['goal', 'asc'],
            ['objective', 'asc'],
            ['report_date', 'asc'],
        ])->values();
    }
}
