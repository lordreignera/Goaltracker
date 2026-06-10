<div class="report-card mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <div class="text-muted small">Africa Renewal Ministries</div>
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
        ['Approved Weekly Reports', $summary['approved_weekly_reports']],
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
                    <div class="text-muted small">{{ $row['department'] }} / {{ $row['unit'] }} / {{ $row['approved_weeks'] }} of {{ $row['planned_weeks'] }} planned reports approved</div>
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
    <h3 class="h5 fw-bold mb-3">Detailed Weekly Reports</h3>
    <div class="{{ ($isPdf ?? false) ? '' : 'table-responsive' }}">
        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>Goal / Objective</th>
                    <th>Week</th>
                    <th>Staff</th>
                    <th>Status</th>
                    <th>Report Details</th>
                    <th>Supervisor Feedback</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($weeklyRows as $row)
                    <tr>
                        <td>
                            <strong>{{ $row['goal'] }}</strong>
                            <div class="text-muted small">{{ $row['objective'] }}</div>
                        </td>
                        <td>
                            Week {{ $row['week_number'] }}
                            <div class="text-muted small">{{ $row['week_starting']?->format('M d, Y') }}</div>
                        </td>
                        <td>{{ $row['staff'] }}</td>
                        <td><span class="badge text-bg-light border">{{ str_replace('_', ' ', $row['status']) }}</span></td>
                        <td>
                            <div><strong>Summary:</strong> {{ $row['summary'] }}</div>
                            @foreach (['Achievements' => $row['achievements'], 'Challenges' => $row['challenges'], 'Recommendations' => $row['recommendations']] as $label => $items)
                                <div class="mt-1"><strong>{{ $label }}:</strong></div>
                                @forelse ($items as $item)
                                    <div class="small">&bull; {{ $item }}</div>
                                @empty
                                    <div class="small text-muted">None provided</div>
                                @endforelse
                            @endforeach
                        </td>
                        <td>{{ $row['review_comments'] ?: 'No feedback yet' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-muted">No weekly reports have been submitted for this quarter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
