<div class="report-card mb-4">
    @php($companySettings = $companySettings ?? \App\Models\CompanySetting::current())
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                @if (! ($isPdf ?? false) && $companySettings->logoUrl())
                    <img src="{{ $companySettings->logoUrl() }}" alt="{{ $companySettings->company_name }} logo" style="width: 34px; height: 34px; object-fit: contain;">
                @endif
                <div class="text-muted small">{{ $companySettings->company_name }}</div>
            </div>
            <h2 class="h4 fw-bold mb-1">{{ $quarter->name }} Quarterly Performance Report</h2>
            <div class="text-muted">{{ $quarter->starts_at->format('M d, Y') }} - {{ $quarter->ends_at->format('M d, Y') }}</div>
        </div>
        <div class="text-md-end">
            <div class="display-6 fw-bold text-success">{{ $summary['average_progress'] }}%</div>
            <div class="text-muted small">Average visible goal progress</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach ([
        ['Goals Planned', $summary['goals_planned']],
        ['Goals Achieved', $summary['goals_achieved']],
        ['Approved Reports', $summary['approved_daily_reports']],
        ['Pending Reviews', $summary['pending_reviews']],
    ] as [$label, $value])
        <div class="col-md-3">
            <div class="report-card h-100">
                <div class="text-muted small">{{ $label }}</div>
                <div class="h3 fw-bold mb-0">{{ $value }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="report-card mb-4">
    <h3 class="h5 fw-bold mb-3">Department Performance</h3>
    @forelse ($departmentRows as $row)
        <div class="chart-row">
            <div class="fw-semibold">{{ $row['department'] }}</div>
            <div class="chart-track"><div class="chart-fill" style="width: {{ $row['progress'] }}%"></div></div>
            <div class="fw-bold">{{ $row['progress'] }}%</div>
        </div>
    @empty
        <p class="text-muted mb-0">No department performance data for this quarter.</p>
    @endforelse
</div>

<div class="report-card mb-4">
    <h3 class="h5 fw-bold mb-3">Goal Progress Flow</h3>
    @forelse ($goalRows as $row)
        <div class="mb-3">
            <div class="d-flex justify-content-between gap-2 mb-1">
                <div>
                    <div class="fw-semibold">{{ $row['title'] }}</div>
                    <div class="text-muted small">{{ $row['department'] }} / {{ $row['section'] }} / {{ $row['unit'] }} / {{ $row['achievement'] }}% average sub-goal achievement</div>
                </div>
                <div class="fw-bold">{{ $row['progress'] }}%</div>
            </div>
            <div class="chart-track"><div class="chart-fill" style="width: {{ $row['progress'] }}%"></div></div>
        </div>
    @empty
        <p class="text-muted mb-0">No goals are visible for this quarter.</p>
    @endforelse
</div>

<div class="report-card mb-4">
    <h3 class="h5 fw-bold mb-3">Reporting Table</h3>
    <div class="{{ ($isPdf ?? false) ? '' : 'table-responsive' }}">
        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>Goal</th>
                    <th>Sub-goal</th>
                    <th>Timeline</th>
                    <th>Frequency</th>
                    <th>Reporting Period</th>
                    <th>Report Date</th>
                    <th>Progress Update</th>
                    <th>Staff Claim</th>
                    <th>Supervisor Verified</th>
                    <th>Achievement</th>
                    <th>Challenges</th>
                    <th>Action Point</th>
                    <th>Evidence</th>
                    <th>Staff</th>
                    <th>Status</th>
                    <th>Supervisor Feedback</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reportRows as $row)
                    <tr>
                        <td><strong>{{ $row['goal'] }}</strong></td>
                        <td>
                            <strong>{{ $row['objective'] }}</strong>
                            <div class="small"><strong>Deliverable / Evidence:</strong> {{ $row['objective_specific_output'] }}</div>
                        </td>
                        <td>{{ $row['timeline'] }}</td>
                        <td>{{ ucfirst($row['reporting_frequency']) }}</td>
                        <td>{{ $row['report_period'] }}</td>
                        <td>{{ $row['report_date']?->format('M d, Y') }}</td>
                        <td>{{ $row['is_progress_update'] ? 'Yes' : 'No' }}</td>
                        <td>{{ $row['achievement_percentage'] !== null ? $row['achievement_percentage'].'%' : 'Not a score update' }}</td>
                        <td>{{ $row['verified_percentage'] !== null ? $row['verified_percentage'].'%' : 'Not verified' }}</td>
                        <td>{{ $row['achievement_summary'] }}</td>
                        <td>{{ $row['challenges'] ?: 'No challenges recorded' }}</td>
                        <td>{{ $row['action_points'] ?: 'No action point recorded' }}</td>
                        <td>{{ $row['evidence_name'] ?: 'No evidence' }}</td>
                        <td>{{ $row['staff'] }}</td>
                        <td><span class="badge text-bg-light border">{{ str_replace('_', ' ', $row['status']) }}</span></td>
                        <td>{{ $row['review_comments'] ?: 'No feedback yet' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="16" class="text-muted">No reports have been submitted for this quarter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
