<?php

namespace App\Http\Requests\Goals;

use App\Models\Quarter;
use App\Models\Section;
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
            'section_ids' => ['nullable', 'array'],
            'section_ids.*' => ['integer', 'exists:sections,id'],
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
            'level' => ['required', 'in:department,section,unit,individual'],
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
                $departmentIds = collect($this->input('department_ids', []))
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();

                $sectionIds = collect($this->input('section_ids', []))
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();

                $unitIds = collect($this->input('unit_ids', []))
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();
                $level = $this->input('level');

                if ($level === 'department' && ($sectionIds->isNotEmpty() || $unitIds->isNotEmpty())) {
                    $validator->errors()->add('level', 'Department goals should only select departments.');
                }

                if ($level === 'section' && $sectionIds->isEmpty()) {
                    $validator->errors()->add('section_ids', 'Section goals must select at least one section.');
                }

                if ($level === 'section' && $unitIds->isNotEmpty()) {
                    $validator->errors()->add('unit_ids', 'Section goals should not also select units.');
                }

                if (in_array($level, ['unit', 'individual'], true) && $unitIds->isEmpty()) {
                    $validator->errors()->add('unit_ids', 'Unit and individual goals must select at least one unit.');
                }

                if (in_array($level, ['unit', 'individual'], true) && $sectionIds->isNotEmpty()) {
                    $validator->errors()->add('section_ids', 'Unit and individual goals should use units only. The section is taken from the selected unit.');
                }

                if ($sectionIds->isNotEmpty()) {
                    $validSections = Section::whereIn('id', $sectionIds->all())
                        ->whereIn('department_id', $departmentIds->all())
                        ->count();

                    if ($validSections !== $sectionIds->count()) {
                        $validator->errors()->add('section_ids', 'Selected sections must belong to the selected departments.');
                    }
                }

                if ($unitIds->isNotEmpty()) {
                    $validUnits = Unit::whereIn('id', $unitIds->all())
                        ->whereIn('department_id', $departmentIds->all())
                        ->when($sectionIds->isNotEmpty(), fn ($query) => $query->whereIn('section_id', $sectionIds->all()))
                        ->count();

                    if ($validUnits !== $unitIds->count()) {
                        $validator->errors()->add('unit_ids', 'Selected units must belong to the selected departments and sections.');
                    }
                }

                if (! $user || $user->isAdmin()) {
                    return;
                }

                if ($departmentIds->count() !== 1 || $departmentIds->first() !== (int) $user->department_id) {
                    $validator->errors()->add('department_ids', 'You can only assign goals to your own department.');
                }

                if ($user->section_id && $sectionIds->isNotEmpty() && ($sectionIds->count() !== 1 || $sectionIds->first() !== (int) $user->section_id)) {
                    $validator->errors()->add('section_ids', 'You can only assign goals to your own section.');
                }

                if ($sectionIds->isNotEmpty()) {
                    $validSections = Section::whereIn('id', $sectionIds->all())
                        ->where('department_id', $user->department_id)
                        ->count();

                    if ($validSections !== $sectionIds->count()) {
                        $validator->errors()->add('section_ids', 'Selected sections must belong to your department.');
                    }
                }

                if ($unitIds->isEmpty()) {
                    return;
                }

                if ($user->unit_id && ($unitIds->count() !== 1 || $unitIds->first() !== (int) $user->unit_id)) {
                    $validator->errors()->add('unit_ids', 'You can only assign goals to your own unit.');

                    return;
                }

                $validUnits = Unit::whereIn('id', $unitIds->all())
                    ->where('department_id', $user->department_id)
                    ->when($user->section_id, fn ($query) => $query->where('section_id', $user->section_id))
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
