<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoalManagementService
{
    public function createGoal(User $user, array $data): Goal
    {
        $data = $this->prepareGoalData($data);

        $this->validateObjectiveWeights($data['objectives']);

        [$departmentIds, $unitIds] = $this->assignmentIds($data);

        $goal = new Goal([
            'quarter_id' => $data['quarter_id'],
            'created_by' => $user->id,
            'owner_id' => $user->id,
            'title' => $data['title'],
            'specific' => $data['specific'] ?? null,
            'measurable' => $data['measurable'] ?? null,
            'achievable' => $data['achievable'] ?? null,
            'relevant' => $data['relevant'] ?? null,
            'time_bound' => $data['time_bound'] ?? null,
            'key_action_steps' => $data['key_action_steps'],
            'primary_metric' => $data['primary_metric'] ?? null,
            'deadline' => $data['deadline'] ?? null,
            'level' => $data['level'],
            'status' => 'draft',
        ]);

        DB::transaction(function () use ($goal, $data, $departmentIds, $unitIds) {
            $goal->save();

            $this->syncAssignments($goal, $departmentIds->all(), $unitIds->all());

            foreach ($data['objectives'] as $objective) {
                $goal->objectives()->create($objective + ['status' => 'pending']);
            }
        });

        return $goal;
    }

    public function updateGoal(Goal $goal, array $data): Goal
    {
        $data = $this->prepareGoalData($data);

        $this->validateObjectiveWeights($data['objectives']);

        [$departmentIds, $unitIds] = $this->assignmentIds($data);

        $keptObjectiveIds = collect($data['objectives'])
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        DB::transaction(function () use ($goal, $data, $departmentIds, $unitIds, $keptObjectiveIds) {
            $goal->update([
                'quarter_id' => $data['quarter_id'],
                'title' => $data['title'],
                'specific' => $data['specific'] ?? null,
                'measurable' => $data['measurable'] ?? null,
                'achievable' => $data['achievable'] ?? null,
                'relevant' => $data['relevant'] ?? null,
                'time_bound' => $data['time_bound'] ?? null,
                'key_action_steps' => $data['key_action_steps'],
                'primary_metric' => $data['primary_metric'] ?? null,
                'deadline' => $data['deadline'] ?? null,
                'level' => $data['level'],
            ]);

            $this->syncAssignments($goal, $departmentIds->all(), $unitIds->all());

            $goal->objectives()
                ->whereNotIn('id', $keptObjectiveIds)
                ->delete();

            foreach ($data['objectives'] as $objectiveData) {
                $objectiveId = $objectiveData['id'] ?? null;
                $payload = collect($objectiveData)->except('id')->all();

                if ($objectiveId) {
                    $goal->objectives()
                        ->whereKey($objectiveId)
                        ->update($payload);
                } else {
                    $goal->objectives()
                        ->create($payload + ['status' => 'pending']);
                }
            }
        });

        return $goal->refresh();
    }

    private function prepareGoalData(array $data): array
    {
        $data['key_action_steps'] = collect($data['key_action_steps'] ?? [])
            ->map(fn ($step) => trim((string) $step))
            ->filter()
            ->values()
            ->all();

        return $data;
    }

    private function validateObjectiveWeights(array $objectives): void
    {
        $objectiveTotal = collect($objectives)
            ->sum(fn ($objective) => (int) $objective['weight']);

        if ($objectiveTotal !== 100) {
            throw ValidationException::withMessages([
                'objectives' => "Objective weights must equal 100%. Current total is {$objectiveTotal}%.",
            ]);
        }
    }

    private function assignmentIds(array $data): array
    {
        $departmentIds = collect($data['department_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $unitIds = collect($data['unit_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        return [$departmentIds, $unitIds];
    }

    private function syncAssignments(Goal $goal, array $departmentIds, array $unitIds): void
    {
        $goal->assignments()->delete();

        $units = Unit::whereIn('id', $unitIds)->get(['id', 'department_id']);
        $unitDepartmentIds = $units->pluck('department_id')->unique();

        foreach (collect($departmentIds)->diff($unitDepartmentIds) as $departmentId) {
            $goal->assignments()->create([
                'department_id' => $departmentId,
            ]);
        }

        $units->each(function (Unit $unit) use ($goal) {
            $goal->assignments()->create([
                'department_id' => $unit->department_id,
                'unit_id' => $unit->id,
            ]);
        });
    }
}