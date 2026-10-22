<?php

namespace App\Http\Controllers\Goals;

use App\Http\Controllers\Controller;
use App\Models\WeeklyUpdate;
use App\Services\GoalAccessService;
use App\Services\WorkflowNotificationService;
use Illuminate\Http\Request;

class SupervisorReviewController extends Controller
{
    public function store(
        Request $request,
        WeeklyUpdate $weeklyUpdate,
        WorkflowNotificationService $notifications
    ) {
        $goal = $weeklyUpdate->objective->goal;
        abort_unless(app(GoalAccessService::class)->canReviewGoal($request->user(), $goal), 403);
        abort_if($weeklyUpdate->user_id === $request->user()->id, 422, 'You cannot review your own report.');
        abort_if($weeklyUpdate->status !== 'submitted', 422, 'Only a submitted report can be reviewed. Ask the author to resubmit it first.');

        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected,revision_requested'],
            'verified_percentage' => [
                $weeklyUpdate->is_progress_update ? 'required_if:decision,approved' : 'nullable',
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],
            'comments' => ['required_if:decision,rejected,revision_requested', 'nullable', 'string', 'max:3000'],
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

        if ($data['decision'] === 'approved') {
            $goal->load('objectives');
            $allObjectivesComplete = $goal->objectives->isNotEmpty()
                && $goal->objectives->every(fn ($objective) => $objective->status === 'completed');

            $goal->update([
                'status' => $allObjectivesComplete ? 'completed' : 'in_progress',
            ]);
        }

        $notifications->reportReviewed($weeklyUpdate, $request->user(), $data['decision']);

        $reviewStatus = match ($data['decision']) {
            'approved' => 'Report approved. Verified progress is now updated.',
            'rejected' => 'Report rejected. The author has been notified.',
            default => 'Revision requested. The author has been notified.',
        };

        return redirect(route('goals.show', $goal).'#report-'.$weeklyUpdate->id)
            ->with('status', $reviewStatus);
    }
}
