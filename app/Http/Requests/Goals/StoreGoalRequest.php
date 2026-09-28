<?php

namespace App\Http\Requests\Goals;

use App\Models\GoalPillar;
use App\Models\Quarter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->canManageGoals();
    }

    public function rules(): array
    {
        return [
            'quarter_id' => ['required', 'exists:quarters,id'],
            'goal_pillar_id' => ['required', 'exists:goal_pillars,id'],
            'department_ids' => ['required', 'array', 'min:1'],
            'department_ids.*' => ['integer', 'distinct', 'exists:departments,id'],
            'objectives' => ['required', 'array', 'min:1'],
            'objectives.*' => ['array'],
            'objectives.*.title' => ['required', 'string', 'max:255'],
            'objectives.*.key_activities' => ['required', 'array', 'min:1'],
            'objectives.*.key_activities.*' => ['required', 'string', 'max:500'],
            'objectives.*.specific_output' => ['required', 'string'],
            'objectives.*.weight' => ['required', 'integer', 'min:1', 'max:100'],
            'objectives.*.planned_weeks' => ['required', 'integer', 'min:1', 'max:13'],
            'objectives.*.reporting_frequency' => ['required', 'array', 'min:1'],
            'objectives.*.reporting_frequency.*' => ['required', 'in:daily,weekly,monthly'],
            'objectives.*.starts_at' => ['required', 'date'],
            'objectives.*.due_at' => ['required', 'date'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $user = $this->user();
                $departmentIds = collect($this->input('department_ids', []))
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();

                if (! $user || $user->isAdmin()) {
                    return;
                }

                if ($departmentIds->diff($user->accessibleDepartmentIds())->isNotEmpty()) {
                    $validator->errors()->add('department_ids', 'You can only assign goals to departments you can access.');
                }

            },
            function (Validator $validator) {
                $objectives = collect($this->input('objectives', []));

                if ($objectives->isEmpty() || $objectives->contains(fn ($objective) => ! is_array($objective) || ! isset($objective['weight']) || ! is_numeric($objective['weight']))) {
                    return;
                }

                $total = $objectives->sum(fn ($objective) => (int) $objective['weight']);

                if ($total !== 100) {
                    $validator->errors()->add('objectives', "Objective weights must total exactly 100%. Current total is {$total}%.");
                }
            },
            function (Validator $validator) {
                $quarter = Quarter::find($this->input('quarter_id'));

                if (! $quarter) {
                    return;
                }

                foreach ($this->input('objectives', []) as $index => $objective) {
                    if (! is_array($objective)) {
                        continue;
                    }

                    $startsAtKey = "objectives.{$index}.starts_at";
                    $dueAtKey = "objectives.{$index}.due_at";
                    $startsAt = $validator->errors()->has($startsAtKey) ? null : $this->date($startsAtKey);
                    $dueAt = empty($objective['due_at']) || $validator->errors()->has($dueAtKey)
                        ? null
                        : $this->date($dueAtKey);
                    $plannedWeeks = (int) ($objective['planned_weeks'] ?? 0);

                    if ($startsAt && ($startsAt->lt($quarter->starts_at) || $startsAt->gt($quarter->ends_at))) {
                        $validator->errors()->add(
                            "objectives.{$index}.starts_at",
                            "Objective start date must be between {$quarter->starts_at->toFormattedDateString()} and {$quarter->ends_at->toFormattedDateString()}."
                        );
                    }

                    if ($dueAt && ($dueAt->lt($quarter->starts_at) || $dueAt->gt($quarter->ends_at))) {
                        $validator->errors()->add(
                            "objectives.{$index}.due_at",
                            "Objective due date must be between {$quarter->starts_at->toFormattedDateString()} and {$quarter->ends_at->toFormattedDateString()}."
                        );
                    }

                    if ($startsAt && $dueAt && $dueAt->lt($startsAt)) {
                        $validator->errors()->add(
                            "objectives.{$index}.due_at",
                            'Objective due date cannot be before its start date.'
                        );
                    }

                    if ($startsAt && $dueAt && $plannedWeeks > 0) {
                        $expectedDueAt = $startsAt->copy()->addDays(($plannedWeeks * 7) - 1);

                        if (! $dueAt->isSameDay($expectedDueAt)) {
                            $validator->errors()->add(
                                "objectives.{$index}.due_at",
                                "Objective due date must match the selected {$plannedWeeks} week duration."
                            );
                        }
                    }
                }
            },
            function (Validator $validator) {
                if (! $this->filled('goal_pillar_id')) {
                    return;
                }

                $isActive = GoalPillar::whereKey($this->input('goal_pillar_id'))
                    ->where('is_active', true)
                    ->exists();

                if (! $isActive) {
                    $validator->errors()->add('goal_pillar_id', 'Select an active goal pillar.');
                }
            },
        ];
    }
}
