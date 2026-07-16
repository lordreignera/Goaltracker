<?php

namespace App\Http\Controllers\Goals;

use App\Http\Controllers\Controller;
use App\Models\WeeklyUpdate;
use App\Services\GoalAccessService;
use Illuminate\Http\Request;

class SupervisorReviewController extends Controller
{
    public function store(Request $request, WeeklyUpdate $weeklyUpdate)
    {
        $goal = $weeklyUpdate->objective->goal;
        abort_unless(app(GoalAccessService::class)->canReviewGoal($request->user(), $goal), 403);

        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected,revision_requested'],
            'verified_percentage' => [
                $weeklyUpdate->is_progress_update ? 'required_if:decision,approved' : 'nullable',
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],
            'comments' => ['nullable', 'string'],
        ]);

        if ($data['decision'] !== 'approved' || ! $weeklyUpdate->is_progress_update) {
            $data['verified_percentage'] = null;
        }

        $weeklyUpdate->reviews()->create($data + ['supervisor_id' => $request->user()->id]);
        $weeklyUpdate->update(['status' => $data['decision']]);

        if ($data['decision'] === 'approved' && $weeklyUpdate->is_progress_update) {
            $weeklyUpdate->objective->update([
                'status' => $data['verified_percentage'] >= 100 ? 'completed' : 'approved',
            ]);
        }

        if ($data['decision'] === 'rejected') {
            $weeklyUpdate->objective->update(['status' => 'rejected']);
        }

        if ($data['decision'] === 'revision_requested') {
            $weeklyUpdate->objective->update(['status' => 'revision_requested']);
        }

        return back()->with('status', 'Review saved.');
    }
}
