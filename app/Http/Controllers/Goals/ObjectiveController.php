<?php

namespace App\Http\Controllers\Goals;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Services\GoalAccessService;
use Illuminate\Http\Request;

class ObjectiveController extends Controller
{
    public function store(Request $request, Goal $goal)
    {
        abort_unless(app(GoalAccessService::class)->canUpdateGoal($request->user(), $goal), 403);

        return redirect()
            ->route('goals.edit', $goal)
            ->withErrors(['objectives' => 'Add or remove objectives from the Edit Goal page so the total weight remains 100%.']);
    }
}
