<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoalManagementService
{
    public function createGoal(User $user, array $data): Goal
    {
        $this->validateObjectiveWeights($data['objectives']);

        $departmentIds = $this->departmentIds($data);

        $goal = new Goal([
            'quarter_id' => $data['quarter_id'],
            'goal_pillar_id' => $data['goal_pillar_id'],
            'created_by' => $user->id,
            'owner_id' => $user->id,
            'status' => 'draft',
        ]);

        DB::transaction(function () use ($goal, $data, $departmentIds) {
            $goal->save();

            $this->syncAssignments($goal, $departmentIds->all());

            foreach ($this->normalizedObjectives($data['objectives']) as $objective) {
                $goal->objectives()->create($objective + ['status' => 'pending']);
            }
        });

        return $goal;
    }

    public function updateGoal(Goal $goal, array $data): Goal
    {
        $this->validateObjectiveWeights($data['objectives']);

        $departmentIds = $this->departmentIds($data);

        $keptObjectiveIds = collect($data['objectives'])
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        DB::transaction(function () use ($goal, $data, $departmentIds, $keptObjectiveIds) {
            $hasReportedObjectiveRemoval = $goal->objectives()
                ->whereNotIn('id', $keptObjectiveIds)
                ->whereHas('weeklyUpdates')
                ->exists();

            if ($hasReportedObjectiveRemoval) {
                throw ValidationException::withMessages([
                    'objectives' => 'Objectives with submitted reports cannot be removed. Keep the objective to preserve its reporting history.',
                ]);
            }

            $goal->update([
                'quarter_id' => $data['quarter_id'],
                'goal_pillar_id' => $data['goal_pillar_id'],
            ]);

            $this->syncAssignments($goal, $departmentIds->all());

            $goal->objectives()
                ->whereNotIn('id', $keptObjectiveIds)
                ->delete();

            foreach ($this->normalizedObjectives($data['objectives']) as $objectiveData) {
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

    public function deleteGoal(Goal $goal): void
    {
        if ($goal->objectives()->whereHas('weeklyUpdates')->exists()) {
            throw ValidationException::withMessages([
                'goal' => 'This work plan has submitted reports and cannot be deleted. Archive it instead to preserve its reporting history.',
            ]);
        }

        $goal->delete();
    }

    private function validateObjectiveWeights(array $objectives): void
    {
        $objectiveTotal = collect($objectives)
            ->sum(fn ($objective) => (int) $objective['weight']);

        if ($objectiveTotal !== 100) {
            throw ValidationException::withMessages([
                'objectives' => "Objective weights must total exactly 100%. Current total is {$objectiveTotal}%.",
            ]);
        }
    }

    private function normalizedObjectives(array $objectives): array
    {
        return collect($objectives)
            ->map(function (array $objective) {
                $objective['key_activities'] = collect($objective['key_activities'] ?? [])
                    ->map(fn ($activity) => trim((string) $activity))
                    ->filter()
                    ->implode(PHP_EOL);
                $objective['reporting_frequency'] = collect($objective['reporting_frequency'] ?? [])
                    ->filter(fn ($frequency) => in_array($frequency, ['daily', 'weekly', 'monthly'], true))
                    ->unique()
                    ->values()
                    ->all();

                return $objective;
            })
            ->all();
    }

    private function departmentIds(array $data)
    {
        return collect($data['department_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    private function syncAssignments(Goal $goal, array $departmentIds): void
    {
        $goal->assignments()->delete();

        foreach ($departmentIds as $departmentId) {
            $goal->assignments()->create([
                'department_id' => $departmentId,
            ]);
        }
    }
}
