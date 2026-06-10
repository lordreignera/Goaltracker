<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoalManagementService
{
    public function __construct(private readonly GoalAccessService $goalAccess)
    {
    }

    public function createGoal(User $user, array $data): Goal
    {
        $this->validateObjectiveWeights($data['objectives']);

        [$departmentIds, $unitIds] = $this->assignmentIds($data);

        $goal = new Goal([
            'quarter_id' => $data['quarter_id'],
            'department_id' => $departmentIds->first(),
            'unit_id' => $unitIds->count() === 1 ? $unitIds->first() : null,
            'owner_id' => $user->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'level' => $data['level'],
            'status' => 'draft',
        ]);

        abort_unless($this->goalAccess->canUpdateGoal($user, $goal), 403);

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
                'department_id' => $departmentIds->first(),
                'unit_id' => $unitIds->count() === 1 ? $unitIds->first() : null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'level' => $data['level'],
            ]);

            $this->syncAssignments($goal, $departmentIds->all(), $unitIds->all());
            $goal->objectives()->whereNotIn('id', $keptObjectiveIds)->delete();

            foreach ($data['objectives'] as $objectiveData) {
                $objectiveId = $objectiveData['id'] ?? null;
                $payload = collect($objectiveData)->except('id')->all();

                if ($objectiveId) {
                    $goal->objectives()->whereKey($objectiveId)->update($payload);
                } else {
                    $goal->objectives()->create($payload + ['status' => 'pending']);
                }
            }
        });

        return $goal->refresh();
    }

    private function validateObjectiveWeights(array $objectives): void
    {
        $objectiveTotal = collect($objectives)->sum(fn ($objective) => (int) $objective['weight']);

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
        $goal->assignedDepartments()->sync($departmentIds);
        $goal->assignedUnits()->sync($unitIds);
    }
}
