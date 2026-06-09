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

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'weight' => ['required', 'integer', 'min:1', 'max:100'],
            'due_at' => ['nullable', 'date'],
        ]);

        $totalWeight = $goal->objectives()->sum('weight') + (int) $data['weight'];
        if ($totalWeight > 100) {
            return back()->withErrors(['weight' => 'Objective weights cannot exceed 100%.'])->withInput();
        }

        $goal->objectives()->create($data);

        return back()->with('status', 'Objective added.');
    }
}
