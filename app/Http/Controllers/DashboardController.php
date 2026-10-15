<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Goal;
use App\Models\GoalPillar;
use App\Models\Quarter;
use App\Models\WeeklyUpdate;
use App\Services\GoalAccessService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, GoalAccessService $access)
    {
        $user = $request->user();
        $goals = Goal::query()
            ->visibleTo($user)
            ->select([
                'id',
                'quarter_id',
                'goal_pillar_id',
                'created_by',
                'owner_id',
                'status',
                'submitted_at',
                'approved_at',
                'approved_by',
                'created_at',
            ])
            ->with([
                'pillar:id,name,annual_goal',
                'quarter:id,name,starts_at,ends_at',
                'assignedDepartments:id,name',
                'objectives' => fn ($query) => $query
                    ->withCount('weeklyUpdates')
                    ->with([
                        'latestWeeklyUpdate.reviews',
                        'latestApprovedUpdate.latestApprovedReview',
                    ]),
            ])
            ->latest()
            ->get();
        $goalPillars = GoalPillar::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $goalProgress = $goals->map(fn (Goal $goal) => $goal->progress());
        $onTrackGoals = $goals->filter(fn (Goal $goal) => $goal->progress() >= 70)->count();
        $atRiskGoals = $goals->filter(fn (Goal $goal) => $goal->progress() >= 40 && $goal->progress() < 70)->count();
        $offTrackGoals = $goals->filter(fn (Goal $goal) => $goal->progress() < 40)->count();
        $organizationScore = null;
        if ($user->isAdmin() || $user->can('view organization dashboard')) {
            $progressByDepartment = $goals
                ->flatMap(fn (Goal $goal) => $goal->assignedDepartments->map(
                    fn (Department $department) => [$department->id, $goal->progress()]
                ))
                ->groupBy(fn (array $row) => $row[0])
                ->map(fn ($rows) => collect($rows)->avg(fn (array $row) => $row[1]));

            $organizationScore = Department::query()
                ->pluck('id')
                ->avg(fn (int $departmentId) => $progressByDepartment->get($departmentId, 0));
        }

        return view('dashboard.index', [
            'totalGoals' => $goals->count(),
            'onTrackGoals' => $onTrackGoals,
            'atRiskGoals' => $atRiskGoals,
            'offTrackGoals' => $offTrackGoals,
            'activeGoals' => $goals->where('status', '!=', 'completed')->count(),
            'completedGoals' => $goals->where('status', 'completed')->count(),
            'averageProgress' => round($goalProgress->avg() ?? 0, 2),
            'organizationScore' => $organizationScore !== null ? round($organizationScore, 1) : null,
            'pendingReviews' => WeeklyUpdate::query()
                ->where('status', 'submitted')
                ->whereHas('objective.goal', fn ($query) => $query->visibleTo($user))
                ->count(),
            'goals' => $goals,
            'goalPillars' => $goalPillars,
            'goalsByPillar' => $goals->whereNotNull('goal_pillar_id')->groupBy('goal_pillar_id'),
            'canCreateGoals' => $user->canCreateGoals(),
            'manageableGoalIds' => $goals
                ->filter(fn (Goal $goal) => $access->canUpdateGoal($user, $goal))
                ->pluck('id'),
            'reportableGoalIds' => $goals
                ->filter(fn (Goal $goal) => $access->canSubmitDailyReport($user, $goal))
                ->pluck('id'),
            'quarters' => Quarter::orderByDesc('starts_at')->get(),
            'departments' => $user->isAdmin()
                ? Department::orderBy('name')->get()
                : Department::whereIn('id', $user->accessibleDepartmentIds())->orderBy('name')->get(),
        ]);
    }
}
