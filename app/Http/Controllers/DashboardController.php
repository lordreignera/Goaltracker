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
        $organizationScore = ($user->isAdmin() || $user->can('view organization dashboard'))
            ? Department::with('goals.objectives')->get()->avg(fn ($department) => $department->goals->avg(fn ($goal) => $goal->progress()) ?? 0)
            : null;

        return view('dashboard.index', [
            'activeGoals' => $goals->where('status', '!=', 'completed')->count(),
            'completedGoals' => $goals->where('status', 'completed')->count(),
            'averageProgress' => round($goalProgress->avg() ?? 0, 2),
            'organizationScore' => $organizationScore !== null ? round($organizationScore, 2) : null,
            'pendingReviews' => WeeklyUpdate::query()
                ->where('status', 'submitted')
                ->whereHas('objective.goal', fn ($query) => $query->visibleTo($user))
                ->count(),
            'goals' => $goals,
        ]);
    }
}
