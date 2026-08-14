<?php

namespace App\Http\Controllers\Goals;

use App\Http\Controllers\Controller;
use App\Http\Requests\Goals\StoreWeeklyUpdateRequest;
use App\Http\Requests\Goals\UpdateWeeklyUpdateRequest;
use App\Models\GoalObjective;
use App\Models\WeeklyUpdate;
use App\Services\GoalAccessService;
use Illuminate\Support\Facades\Storage;

class WeeklyUpdateController extends Controller
{
    public function store(StoreWeeklyUpdateRequest $request, GoalObjective $objective)
    {
        $objective->weeklyUpdates()->create($request->preparedUpdateData() + ['user_id' => $request->user()->id]);

        return back()->with('status', 'Daily report submitted.');
    }

    public function update(UpdateWeeklyUpdateRequest $request, WeeklyUpdate $weeklyUpdate)
    {
        $data = $request->preparedUpdateData() + ['status' => 'submitted'];
        $oldEvidencePath = $weeklyUpdate->evidence_path;

        $weeklyUpdate->update($data);

        if ($oldEvidencePath && isset($data['evidence_path'])) {
            Storage::disk(config('filesystems.evidence_disk'))->delete($oldEvidencePath);
        }

        if (in_array($weeklyUpdate->objective->status, ['rejected', 'revision_requested'], true)) {
            $weeklyUpdate->objective->update(['status' => 'pending']);
        }

        return back()->with('status', 'Daily report resubmitted.');
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
