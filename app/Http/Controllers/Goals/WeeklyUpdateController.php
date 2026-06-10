<?php

namespace App\Http\Controllers\Goals;

use App\Http\Controllers\Controller;
use App\Http\Requests\Goals\StoreWeeklyUpdateRequest;
use App\Http\Requests\Goals\UpdateWeeklyUpdateRequest;
use App\Models\GoalObjective;
use App\Models\WeeklyUpdate;

class WeeklyUpdateController extends Controller
{
    public function store(StoreWeeklyUpdateRequest $request, GoalObjective $objective)
    {
        $objective->weeklyUpdates()->create($request->preparedUpdateData() + ['user_id' => $request->user()->id]);

        return back()->with('status', 'Weekly update submitted.');
    }

    public function update(UpdateWeeklyUpdateRequest $request, WeeklyUpdate $weeklyUpdate)
    {
        $weeklyUpdate->update($request->preparedUpdateData() + ['status' => 'submitted']);

        if (in_array($weeklyUpdate->objective->status, ['rejected', 'revision_requested'], true)) {
            $weeklyUpdate->objective->update(['status' => 'pending']);
        }

        return back()->with('status', 'Weekly update resubmitted.');
    }
}
