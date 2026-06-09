<?php

namespace App\Http\Controllers\Goals;

use App\Http\Controllers\Controller;
use App\Models\GoalObjective;
use App\Services\GoalAccessService;
use Illuminate\Http\Request;

class WeeklyUpdateController extends Controller
{
    public function store(Request $request, GoalObjective $objective)
    {
        $goal = $objective->goal;
        abort_unless(app(GoalAccessService::class)->canViewGoal($request->user(), $goal), 403);

        $objective->weeklyUpdates()->create($request->validate([
            'week_number' => ['required', 'integer', 'min:1', 'max:13'],
            'week_starting' => ['nullable', 'date'],
            'progress_summary' => ['required', 'string'],
            'achievements' => ['nullable', 'string'],
            'challenges' => ['nullable', 'string'],
            'next_actions' => ['nullable', 'string'],
            'percentage_estimate' => ['required', 'integer', 'min:0', 'max:100'],
        ]) + ['user_id' => $request->user()->id]);

        return back()->with('status', 'Weekly update submitted.');
    }
}
