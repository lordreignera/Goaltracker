@php
    $keyActivities = $objective['key_activities'] ?? [''];
    $keyActivities = is_array($keyActivities)
        ? $keyActivities
        : preg_split('/\r\n|\r|\n/', (string) $keyActivities);
    $keyActivities = collect($keyActivities)->map(fn ($activity) => trim((string) $activity))->filter()->values()->all() ?: [''];
    $reportingFrequencies = $objective['reporting_frequency'] ?? ['weekly'];
    $reportingFrequencies = is_array($reportingFrequencies) ? $reportingFrequencies : [$reportingFrequencies];
@endphp

<div class="objective-row" data-objective-row>
    @if ($showId ?? false)
        <input type="hidden" name="objectives[{{ $index }}][id]" value="{{ $objective['id'] ?? '' }}">
    @endif
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong class="small">Work Plan Goal {{ $index + 1 }}</strong>
        <button class="btn btn-sm btn-outline-danger" type="button" data-remove-objective>Remove</button>
    </div>
    <div class="row g-2">
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Goal Name</label>
            <input class="form-control" name="objectives[{{ $index }}][title]" value="{{ $objective['title'] ?? '' }}" placeholder="Goal name" required>
        </div>
        <div class="col-md-2">
            <input class="form-control" type="number" min="1" max="100" name="objectives[{{ $index }}][weight]" value="{{ $objective['weight'] ?? '' }}" placeholder="Weight %" required>
        </div>
        <div class="col-md-2">
            <select class="form-select" name="objectives[{{ $index }}][planned_weeks]" data-objective-weeks required>
                <option value="">Weeks</option>
                @for ($week = 1; $week <= 13; $week++)
                    <option value="{{ $week }}" @selected(($objective['planned_weeks'] ?? '') == $week)>{{ $week }} week{{ $week === 1 ? '' : 's' }}</option>
                @endfor
            </select>
            <small class="text-muted">Planned duration</small>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Report Cadence</label>
            @foreach (['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'] as $value => $label)
                <label class="form-check">
                    <input class="form-check-input" type="checkbox" name="objectives[{{ $index }}][reporting_frequency][]" value="{{ $value }}" @checked(in_array($value, $reportingFrequencies, true))>
                    <span class="form-check-label">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        <div class="col-md-2">
            <input class="form-control" type="date" name="objectives[{{ $index }}][starts_at]" value="{{ $objective['starts_at'] ?? '' }}" required>
            <small class="text-muted" data-objective-date-help>Start date</small>
        </div>
        <div class="col-md-2">
            <input class="form-control" type="date" name="objectives[{{ $index }}][due_at]" value="{{ $objective['due_at'] ?? '' }}" readonly required>
            <small class="text-muted" data-objective-date-help>Auto end date</small>
        </div>
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                <label class="form-label small fw-semibold mb-0">Key Activities</label>
                <button class="btn btn-sm btn-outline-secondary" type="button" data-add-key-activity>Add Activity</button>
            </div>
            <div class="d-grid gap-2" data-key-activities-list>
                @foreach ($keyActivities as $activityIndex => $activity)
                    <div class="key-activity-row" data-key-activity-row>
                        <input class="form-control" name="objectives[{{ $index }}][key_activities][{{ $activityIndex }}]" value="{{ $activity }}" placeholder="Key activity {{ $activityIndex + 1 }}" required>
                        <button class="btn btn-outline-danger" type="button" data-remove-key-activity>Remove</button>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="col-12">
            <label class="form-label small fw-semibold">Deliverable</label>
            <textarea class="form-control" name="objectives[{{ $index }}][specific_output]" rows="3" placeholder="What result, deliverable, or evidence should be produced?" required>{{ $objective['specific_output'] ?? '' }}</textarea>
        </div>
        <div class="col-12">
            <div class="small fw-semibold text-muted" data-planned-weeks-preview>Choose start and due dates to preview planned reporting weeks.</div>
        </div>
    </div>
</div>
