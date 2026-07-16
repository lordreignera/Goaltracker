<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Goal;
use App\Models\WeeklyUpdate;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $goals = Goal::query()->visibleTo($user)->with('objectives')->get();

        $goalProgress = $goals->map(fn (Goal $goal) => $goal->progress());
        $onTrackGoals = $goals->filter(fn (Goal $goal) => $goal->progress() >= 70)->count();
        $atRiskGoals = $goals->filter(fn (Goal $goal) => $goal->progress() >= 40 && $goal->progress() < 70)->count();
        $offTrackGoals = $goals->filter(fn (Goal $goal) => $goal->progress() < 40)->count();
        $organizationScore = ($user->isAdmin() || $user->can('view organization dashboard'))
            ? Department::with('goals.objectives')->get()->avg(fn ($department) => $department->goals->avg(fn ($goal) => $goal->progress()) ?? 0)
            : null;

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
        ]);
    }
}
