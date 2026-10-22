<?php

namespace App\Http\Controllers\Goals;

use App\Http\Controllers\Controller;
use App\Http\Requests\Goals\StoreWeeklyUpdateRequest;
use App\Http\Requests\Goals\UpdateWeeklyUpdateRequest;
use App\Models\GoalObjective;
use App\Models\WeeklyUpdate;
use App\Services\GoalAccessService;
use App\Services\WorkflowNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WeeklyUpdateController extends Controller
{
    public function store(
        StoreWeeklyUpdateRequest $request,
        GoalObjective $objective,
        WorkflowNotificationService $notifications
    ) {
        $update = $objective->weeklyUpdates()->create($request->preparedUpdateData() + ['user_id' => $request->user()->id]);
        $notifications->reportSubmitted($update, $request->user());

        return redirect(route('goals.show', $objective->goal).'#report-'.$update->id)
            ->with('status', 'Report added successfully and awaiting supervisor approval.')
            ->with('report_notice_id', $update->id);
    }

    public function update(
        UpdateWeeklyUpdateRequest $request,
        WeeklyUpdate $weeklyUpdate,
        WorkflowNotificationService $notifications
    ) {
        $data = $request->preparedUpdateData() + ['status' => 'submitted'];
        $oldEvidencePath = $weeklyUpdate->evidence_path;

        $weeklyUpdate->update($data);

        if ($oldEvidencePath && isset($data['evidence_path'])) {
            Storage::disk(config('filesystems.evidence_disk'))->delete($oldEvidencePath);
        }

        if (in_array($weeklyUpdate->objective->status, ['rejected', 'revision_requested'], true)) {
            $weeklyUpdate->objective->update(['status' => 'pending']);
        }

        $notifications->reportSubmitted($weeklyUpdate, $request->user(), true);

        return redirect(route('goals.show', $weeklyUpdate->objective->goal).'#report-'.$weeklyUpdate->id)
            ->with('status', 'Report updated and awaiting supervisor approval.')
            ->with('report_notice_id', $weeklyUpdate->id);
    }

    public function destroy(Request $request, WeeklyUpdate $weeklyUpdate, GoalAccessService $access)
    {
        $objective = $weeklyUpdate->objective;

        abort_unless(
            $weeklyUpdate->user_id === $request->user()->id
                && $weeklyUpdate->status !== 'approved'
                && $access->canSubmitDailyReport($request->user(), $objective->goal),
            403
        );

        $evidencePath = $weeklyUpdate->evidence_path;
        $deletedStatus = $weeklyUpdate->status;

        DB::transaction(function () use ($weeklyUpdate, $objective, $deletedStatus) {
            $weeklyUpdate->reviews()->get()->each->delete();
            $weeklyUpdate->delete();

            if (in_array($deletedStatus, ['rejected', 'revision_requested'], true)
                && $objective->status === $deletedStatus) {
                $latestReviewed = $objective->weeklyUpdates()
                    ->whereIn('status', ['rejected', 'revision_requested'])
                    ->latest('updated_at')
                    ->first();
                $newStatus = $latestReviewed?->status;

                if (! $newStatus) {
                    $hasApproved = $objective->approvedWeeklyUpdates()->exists();
                    $newStatus = $hasApproved
                        ? ($objective->progressPercent() >= 100 ? 'completed' : 'approved')
                        : 'pending';
                }

                $objective->update(['status' => $newStatus]);
            }
        });

        if ($evidencePath) {
            Storage::disk(config('filesystems.evidence_disk'))->delete($evidencePath);
        }

        return redirect(route('goals.show', $objective->goal).'#report-objective-'.$objective->id)
            ->with('status', 'Report deleted. You can add another report for this period.')
            ->with('report_deleted_objective_id', $objective->id);
    }

    public function evidence(WeeklyUpdate $weeklyUpdate, GoalAccessService $access)
    {
        abort_unless(
            $weeklyUpdate->hasEvidence()
                && $access->canViewGoal(request()->user(), $weeklyUpdate->objective->goal),
            404
        );

        return Storage::disk(config('filesystems.evidence_disk'))->download(
            $weeklyUpdate->evidence_path,
            $weeklyUpdate->evidence_original_name
        );
    }
}
