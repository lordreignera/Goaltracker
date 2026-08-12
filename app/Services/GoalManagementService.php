<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\Section;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoalManagementService
{
    public function createGoal(User $user, array $data): Goal
    {
        $this->validateObjectiveWeights($data['objectives']);

        [$departmentIds, $sectionIds, $unitIds] = $this->assignmentIds($data);

        $goal = new Goal([
            'quarter_id' => $data['quarter_id'],
            'goal_pillar_id' => $data['goal_pillar_id'],
            'created_by' => $user->id,
            'owner_id' => $user->id,
            'title' => $data['title'],
            'level' => $data['level'],
            'status' => 'draft',
        ]);

        DB::transaction(function () use ($goal, $data, $departmentIds, $sectionIds, $unitIds) {
            $goal->save();

            $this->syncAssignments($goal, $departmentIds->all(), $sectionIds->all(), $unitIds->all());

            foreach ($this->normalizedObjectives($data['objectives']) as $objective) {
                $goal->objectives()->create($objective + ['status' => 'pending']);
            }
        });

        return $goal;
    }

    public function updateGoal(Goal $goal, array $data): Goal
    {
        $this->validateObjectiveWeights($data['objectives']);

        [$departmentIds, $sectionIds, $unitIds] = $this->assignmentIds($data);

        $keptObjectiveIds = collect($data['objectives'])
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        DB::transaction(function () use ($goal, $data, $departmentIds, $sectionIds, $unitIds, $keptObjectiveIds) {
            $goal->update([
                'quarter_id' => $data['quarter_id'],
                'goal_pillar_id' => $data['goal_pillar_id'],
                'title' => $data['title'],
                'level' => $data['level'],
            ]);

            $this->syncAssignments($goal, $departmentIds->all(), $sectionIds->all(), $unitIds->all());

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

        $sectionIds = collect($data['section_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        return [$departmentIds, $sectionIds, $unitIds];
    }

    private function syncAssignments(Goal $goal, array $departmentIds, array $sectionIds, array $unitIds): void
    {
        $goal->assignments()->delete();

        $units = Unit::whereIn('id', $unitIds)->get(['id', 'department_id', 'section_id']);
        $sections = Section::whereIn('id', $sectionIds)->get(['id', 'department_id']);
        $unitDepartmentIds = $units->pluck('department_id')->unique();
        $unitSectionIds = $units->pluck('section_id')->filter()->unique();
        $sectionDepartmentIds = $sections->pluck('department_id')->unique();

        foreach (collect($departmentIds)->diff($unitDepartmentIds)->diff($sectionDepartmentIds) as $departmentId) {
            $goal->assignments()->create([
                'department_id' => $departmentId,
            ]);
        }

        $sections->whereNotIn('id', $unitSectionIds)->each(function (Section $section) use ($goal) {
            $goal->assignments()->create([
                'department_id' => $section->department_id,
                'section_id' => $section->id,
            ]);
        });

        $units->each(function (Unit $unit) use ($goal) {
            $goal->assignments()->create([
                'department_id' => $unit->department_id,
                'section_id' => $unit->section_id,
                'unit_id' => $unit->id,
            ]);
        });
    }
}
