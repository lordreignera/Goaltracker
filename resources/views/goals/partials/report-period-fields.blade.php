@php
    $reportingFrequencies = $objective->reportingFrequencies();
    $periodsByFrequency = $objective->reportingPeriods();
    $selectedPeriod = ($useOldInput ?? true)
        ? old('report_period_start', $selectedPeriod ?? '')
        : ($selectedPeriod ?? '');
    $initialFrequency = ($useOldInput ?? true)
        ? old('reporting_frequency', $selectedFrequency ?? $reportingFrequencies[0])
        : ($selectedFrequency ?? $reportingFrequencies[0]);
    $initialPeriods = $periodsByFrequency[$initialFrequency] ?? [];
    $initialPeriod = collect($initialPeriods)->contains('start', $selectedPeriod)
        ? $selectedPeriod
        : ($initialPeriods[0]['start'] ?? '');
@endphp

<div class="{{ $columnClass ?? 'col-md-4' }}" data-report-period-picker data-report-periods='@json($periodsByFrequency)' data-selected-period="{{ $selectedPeriod }}">
    <label class="form-label small fw-semibold">Reporting Period</label>
    <select class="form-select{{ ($selectSize ?? '') === 'sm' ? ' form-select-sm' : '' }}" name="report_period_start" data-report-period required>
        <option value="" disabled @selected(! $initialPeriod)>Select reporting period</option>
        @foreach ($initialPeriods as $period)
            <option value="{{ $period['start'] }}" @selected($initialPeriod === $period['start'])>{{ $period['label'] }}</option>
        @endforeach
    </select>
    <input type="hidden" name="report_date" value="{{ $initialPeriod }}" data-report-date>
    <small class="text-muted" data-report-period-help>Select the period this report covers.</small>
    @if ($useOldInput ?? true)
        @error('report_period_start')
            <small class="d-block text-danger mt-1">{{ $message }}</small>
        @enderror
        @error('report_date')
            <small class="d-block text-danger mt-1">{{ $message }}</small>
        @enderror
    @endif
</div>
