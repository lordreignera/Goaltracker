<?php

namespace App\Http\Requests\Goals;

use App\Models\Quarter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->isAdmin() || $user->isSupervisor());
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
            'description' => ['nullable', 'string'],
            'level' => ['required', 'in:department,unit,individual'],
            'objectives' => ['required', 'array', 'min:1'],
            'objectives.*.title' => ['required', 'string', 'max:255'],
            'objectives.*.description' => ['nullable', 'string'],
            'objectives.*.weight' => ['required', 'integer', 'min:1', 'max:100'],
            'objectives.*.starts_at' => ['required', 'date'],
            'objectives.*.due_at' => ['nullable', 'date'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $quarter = Quarter::find($this->input('quarter_id'));

                if (! $quarter) {
                    return;
                }

                foreach ($this->input('objectives', []) as $index => $objective) {
                    $startsAt = $this->date("objectives.{$index}.starts_at");
                    $dueAt = empty($objective['due_at'])
                        ? null
                        : $this->date("objectives.{$index}.due_at");

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
                }
            },
        ];
    }
}
