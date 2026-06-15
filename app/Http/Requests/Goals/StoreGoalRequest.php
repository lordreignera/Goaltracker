<?php

namespace App\Http\Requests\Goals;

use App\Models\Quarter;
use App\Models\Unit;
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
            'department_ids' => ['required', 'array', 'min:1'],
            'department_ids.*' => ['integer', 'exists:departments,id'],
            'unit_ids' => ['nullable', 'array'],
            'unit_ids.*' => ['integer', 'exists:units,id'],
            'title' => ['required', 'string', 'max:255'],
            'specific' => ['required', 'string'],
            'measurable' => ['required', 'string'],
            'achievable' => ['required', 'string'],
            'relevant' => ['required', 'string'],
            'time_bound' => ['required', 'string'],
            'key_action_steps' => ['nullable'],
            'key_action_steps.*' => ['string'],
            'primary_metric' => ['required', 'string', 'max:255'],
            'deadline' => ['required', 'date'],
            'level' => ['required', 'in:department,unit,individual'],
            'objectives' => ['required', 'array', 'min:1'],
            'objectives.*.title' => ['required', 'string', 'max:255'],
            'objectives.*.specific_output' => ['required', 'string'],
            'objectives.*.success_measure' => ['required', 'string'],
            'objectives.*.weight' => ['required', 'integer', 'min:1', 'max:100'],
            'objectives.*.planned_weeks' => ['required', 'integer', 'min:1', 'max:13'],
            'objectives.*.starts_at' => ['required', 'date'],
            'objectives.*.due_at' => ['required', 'date'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $user = $this->user();

                if (! $user || $user->isAdmin()) {
                    return;
                }

                $departmentIds = collect($this->input('department_ids', []))
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();

                if ($departmentIds->count() !== 1 || $departmentIds->first() !== (int) $user->department_id) {
                    $validator->errors()->add('department_ids', 'You can only assign goals to your own department.');
                }

                $unitIds = collect($this->input('unit_ids', []))
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();

                if ($unitIds->isEmpty()) {
                    return;
                }

                if ($user->unit_id && ($unitIds->count() !== 1 || $unitIds->first() !== (int) $user->unit_id)) {
                    $validator->errors()->add('unit_ids', 'You can only assign goals to your own unit.');

                    return;
                }

                $validUnits = Unit::whereIn('id', $unitIds->all())
                    ->where('department_id', $user->department_id)
                    ->count();

                if ($validUnits !== $unitIds->count()) {
                    $validator->errors()->add('unit_ids', 'Selected units must belong to your department.');
                }
            },
            function (Validator $validator) {
                $quarter = Quarter::find($this->input('quarter_id'));

                if (! $quarter) {
                    return;
                }

                $deadline = $this->date('deadline');

                if ($deadline && ($deadline->lt($quarter->starts_at) || $deadline->gt($quarter->ends_at))) {
                    $validator->errors()->add(
                        'deadline',
                        "Goal deadline must be between {$quarter->starts_at->toFormattedDateString()} and {$quarter->ends_at->toFormattedDateString()}."
                    );
                }

                foreach ($this->input('objectives', []) as $index => $objective) {
                    $startsAt = $this->date("objectives.{$index}.starts_at");
                    $dueAt = empty($objective['due_at'])
                        ? null
                        : $this->date("objectives.{$index}.due_at");
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
        ];
    }
}
