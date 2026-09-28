<?php

namespace App\Http\Requests\Admin;

use App\Models\Quarter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreQuarterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) ($this->user()?->isAdmin() || $this->user()?->can('manage quarters'));
    }

    public function rules(): array
    {
        $quarter = $this->route('quarter');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('quarters', 'name')->ignore($quarter)],
            'starts_at' => ['required', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $this->filled('starts_at') || $validator->errors()->has('starts_at')) {
                    return;
                }

                $startsAt = Carbon::parse($this->input('starts_at'))->startOfDay();
                $endsAt = $startsAt->copy()->addDays(89);
                $quarter = $this->route('quarter');

                $overlaps = Quarter::query()
                    ->when($quarter, fn ($query) => $query->whereKeyNot($quarter->id))
                    ->whereDate('starts_at', '<=', $endsAt->toDateString())
                    ->whereDate('ends_at', '>=', $startsAt->toDateString())
                    ->exists();

                if ($overlaps) {
                    $validator->errors()->add('starts_at', 'This quarter overlaps an existing quarter. Choose a start date outside existing quarter dates.');
                }
            },
        ];
    }
}
