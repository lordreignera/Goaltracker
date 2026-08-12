<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">{{ $goal->title }}</h1>
    </x-slot>

    <style>
        .goal-summary-actions {
            min-width: 220px;
        }

        .goal-action-stack {
            display: grid;
            gap: 8px;
            justify-items: end;
        }

        .daily-report-form {
            background: #fbfcfd;
        }

        @media (max-width: 767.98px) {
            .goal-summary {
                flex-direction: column;
            }

            .goal-summary-actions {
                width: 100%;
                min-width: 0;
                text-align: left !important;
            }

            .goal-action-stack {
                justify-items: stretch;
            }

            .review-form {
                align-items: stretch;
            }
        }
    </style>

    <div class="d-grid gap-4">
            <div class="bg-white border rounded-3 p-4 mb-4">
                <div class="goal-summary d-flex justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <div class="text-muted">
                            {{ $goal->quarter->name }} /
                            {{ $goal->pillar?->name ?? 'Unassigned pillar' }} /
                            {{ $goal->assignedDepartments->pluck('name')->unique()->join(', ') }} /
                            {{ $goal->assignedSections->isNotEmpty() ? $goal->assignedSections->pluck('name')->unique()->join(', ') : 'Department-wide' }} /
                            {{ $goal->assignedUnits->isNotEmpty() ? $goal->assignedUnits->pluck('name')->unique()->join(', ') : 'All units' }}
                        </div>

                        <div class="mt-3">
                            <strong>Goal Set:</strong> {{ $goal->title }}
                        </div>
                    </div>

                    <div class="goal-summary-actions text-end">
                        <div class="h2 mb-1">{{ $goal->progress() }}%</div>
                        <small class="text-muted d-block mb-2">Completed approved objective weight</small>

                        <div class="progress">
                            <div class="progress-bar" style="width: {{ $goal->progress() }}%"></div>
                        </div>

                        @if ($canUpdateGoal)
                            <div class="goal-action-stack mt-3">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('goals.edit', $goal) }}">
                                    Edit Goal Set
                                </a>

                                <form method="post" action="{{ route('goals.submit', $goal) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success w-100">Submit Goal</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @foreach ($goal->objectives as $objective)
                @php
                    $objectiveIsApprovedComplete = $objective->isApprovedComplete();
                    $objectiveStatusLabel = $objectiveIsApprovedComplete ? 'completed' : $objective->status;
                    $totalWeeks = $objective->totalReportingWeeks();
                    $achievementProgress = $objective->progressPercent();
                    $objectiveContribution = round($objective->progressContribution(), 1);
                    $reportingFrequencies = $objective->reportingFrequencies();
                    [$firstReportDate, $lastReportDate] = $objective->reportingDateRange();
                    $minReportDate = $firstReportDate?->toDateString();
                    $maxReportDate = $lastReportDate?->toDateString();
                @endphp

                <div class="bg-white border rounded-3 p-4 mb-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                        <div>
                            <h2 class="h5 mb-1">{{ $objective->title }}</h2>

                            <div class="text-muted small">
                                {{ $objective->planned_weeks }} planned week{{ $objective->planned_weeks === 1 ? '' : 's' }}
                                / {{ $objective->starts_at?->format('M d, Y') }} - {{ $objective->due_at?->format('M d, Y') }}
                                / {{ collect($reportingFrequencies)->map(fn ($frequency) => ucfirst($frequency))->join(', ') }} reporting
                            </div>

                            <div class="mt-2">
                                <div>
                                    <strong>Key Activities:</strong>
                                    <ul class="mb-0 mt-1">
                                        @foreach ($objective->keyActivitiesList() as $activity)
                                            <li>{{ $activity }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="mt-1"><strong>Key Result Areas / Deliverables:</strong> {{ $objective->specific_output }}</div>
                            </div>
                        </div>

                        <span class="badge text-bg-{{ $objectiveIsApprovedComplete ? 'success' : 'secondary' }} align-self-start">
                            {{ str_replace('_', ' ', $objectiveStatusLabel) }} / {{ $objectiveContribution }}% of {{ $objective->weight }}%
                        </span>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>{{ $achievementProgress }}% latest supervisor-verified achievement across {{ $totalWeeks }} planned week{{ $totalWeeks === 1 ? '' : 's' }}</span>
                            <span>{{ $achievementProgress }}%</span>
                        </div>

                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar" style="width: {{ $achievementProgress }}%"></div>
                        </div>
                    </div>

                    <form method="post" action="{{ route('objectives.weekly-updates.store', $objective) }}" class="daily-report-form border rounded-3 p-3 p-md-4 mb-3" enctype="multipart/form-data">
                        @csrf

                        @if ($errors->any())
                            <div class="alert alert-danger py-2 small">
                                Please check the report fields. Dates must stay within the strategic goal/objective timeline, and progress updates need an achievement percentage.
                            </div>
                        @endif

                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Report Date</label>
                                <input class="form-control" type="date" name="report_date" min="{{ $minReportDate }}" max="{{ $maxReportDate }}" required>
                                <small class="text-muted">
                                    {{ $firstReportDate?->format('M d, Y') }} to {{ $lastReportDate?->format('M d, Y') }}
                                </small>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Report Cadence</label>
                                <select class="form-select" name="reporting_frequency" required>
                                    @foreach ($reportingFrequencies as $frequency)
                                        <option value="{{ $frequency }}">{{ ucfirst($frequency) }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Choose what this report covers.</small>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Progress Score</label>
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox" name="is_progress_update" value="1" id="progress-update-{{ $objective->id }}">
                                    <label class="form-check-label small" for="progress-update-{{ $objective->id }}">This report updates progress</label>
                                </div>
                                <input class="form-control" type="number" name="achievement_percentage" min="0" max="100" placeholder="Achievement %" data-progress-score disabled>
                                <small class="text-muted">Required only when updating progress.</small>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label small fw-semibold">Evidence Document</label>
                                <input class="form-control" type="file" name="evidence_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                                <small class="text-muted">Optional PDF, Word, Excel, or image file. Max 10MB.</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold">Achievement</label>
                                <textarea class="form-control" name="achievement_summary" rows="3" placeholder="What was achieved on this report date?" required></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Challenges</label>
                                <textarea class="form-control" name="challenges" rows="3" placeholder="What blocked or slowed progress?"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Action Point / Next Step</label>
                                <textarea class="form-control" name="action_points" rows="3" placeholder="What should happen next, by whom, or what support is needed?"></textarea>
                            </div>
                        </div>

                        <button class="btn btn-sm btn-primary mt-3">Submit Report</button>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Report Date</th>
                                    <th>Cadence</th>
                                    <th>Reporting Period</th>
                                    <th>Progress Update</th>
                                    <th>Staff Claim</th>
                                    <th>Supervisor Verified</th>
                                    <th>Achievement</th>
                                    <th>Challenges</th>
                                    <th>Action Point</th>
                                    <th>Evidence</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($objective->weeklyUpdates as $update)
                                    @php($canEditUpdate = $update->user_id === auth()->id() && $update->status !== 'approved')
                                    @php($verifiedPercentage = $update->verifiedAchievementPercent())
                                    <tr>
                                        <td>{{ $update->report_date?->format('M d, Y') }}</td>
                                        <td>{{ ucfirst($update->reporting_frequency ?? 'weekly') }}</td>
                                        <td>{{ $update->report_period_start?->format('M d, Y') }} - {{ $update->report_period_end?->format('M d, Y') }}</td>
                                        <td>{{ $update->is_progress_update ? 'Yes' : 'No' }}</td>
                                        <td>{{ $update->achievement_percentage !== null ? $update->achievement_percentage.'%' : 'Not a score update' }}</td>
                                        <td>{{ $verifiedPercentage !== null ? $verifiedPercentage.'%' : 'Not verified' }}</td>
                                        <td>{{ $update->achievement_summary }}</td>
                                        <td>{{ $update->challenges ?: 'No challenges recorded' }}</td>
                                        <td>{{ $update->action_points ?: 'No action point recorded' }}</td>
                                        <td>
                                            @if ($update->hasEvidence())
                                                <a href="{{ route('weekly-updates.evidence', $update) }}">{{ $update->evidence_original_name ?? 'Download evidence' }}</a>
                                            @else
                                                <span class="text-muted">No evidence</span>
                                            @endif
                                        </td>
                                        <td><span class="badge text-bg-light border">{{ str_replace('_', ' ', $update->status) }}</span></td>
                                        <td>
                                            @if ($canEditUpdate)
                                                <details>
                                                    <summary class="btn btn-sm btn-outline-secondary">Edit</summary>
                                                    <form method="post" action="{{ route('weekly-updates.update', $update) }}" class="border rounded-3 p-3 mt-2" enctype="multipart/form-data">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="row g-2">
                                                            <div class="col-md-4">
                                                                <label class="form-label small fw-semibold">Report Date</label>
                                                                <input class="form-control form-control-sm" type="date" name="report_date" min="{{ $minReportDate }}" max="{{ $maxReportDate }}" value="{{ old('report_date', $update->report_date?->toDateString()) }}" required>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label small fw-semibold">Report Cadence</label>
                                                                <select class="form-select form-select-sm" name="reporting_frequency" required>
                                                                    @foreach ($reportingFrequencies as $frequency)
                                                                        <option value="{{ $frequency }}" @selected(old('reporting_frequency', $update->reporting_frequency ?? 'weekly') === $frequency)>{{ ucfirst($frequency) }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label small fw-semibold">Progress Score</label>
                                                                <div class="form-check mb-1">
                                                                    <input class="form-check-input" type="checkbox" name="is_progress_update" value="1" id="edit-progress-update-{{ $update->id }}" @checked(old('is_progress_update', $update->is_progress_update))>
                                                                    <label class="form-check-label small" for="edit-progress-update-{{ $update->id }}">Updates progress</label>
                                                                </div>
                                                                <input class="form-control form-control-sm" type="number" name="achievement_percentage" min="0" max="100" value="{{ old('achievement_percentage', $update->achievement_percentage) }}" placeholder="Achievement %" data-progress-score @disabled(! old('is_progress_update', $update->is_progress_update))>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <label class="form-label small fw-semibold">Replace Evidence Document</label>
                                                                <input class="form-control form-control-sm" type="file" name="evidence_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                                                                @if ($update->hasEvidence())
                                                                    <small class="text-muted">Current: {{ $update->evidence_original_name ?? 'Evidence uploaded' }}</small>
                                                                @endif
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label small fw-semibold">Achievement</label>
                                                                <textarea class="form-control form-control-sm" name="achievement_summary" rows="2" required>{{ old('achievement_summary', $update->achievement_summary) }}</textarea>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-semibold">Challenges</label>
                                                                <textarea class="form-control form-control-sm" name="challenges" rows="2">{{ old('challenges', $update->challenges) }}</textarea>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-semibold">Action Point / Next Step</label>
                                                                <textarea class="form-control form-control-sm" name="action_points" rows="2">{{ old('action_points', $update->action_points) }}</textarea>
                                                            </div>
                                                        </div>
                                                        <button class="btn btn-sm btn-primary mt-2">Save Changes</button>
                                                    </form>
                                                </details>
                                            @endif

                                            @if ($canReviewGoal)
                                                <form method="post" action="{{ route('weekly-updates.reviews.store', $update) }}" class="review-form row g-2 mt-2">
                                                    @csrf
                                                    <div class="col-md-4 col-lg-3">
                                                        <select class="form-select form-select-sm" name="decision" required>
                                                            <option value="approved">Approve</option>
                                                            <option value="rejected">Reject</option>
                                                            <option value="revision_requested">Request revision</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 col-lg-3">
                                                        <input class="form-control form-control-sm" type="number" name="verified_percentage" min="0" max="100" value="{{ $update->achievement_percentage }}" placeholder="{{ $update->is_progress_update ? 'Verified %' : 'No score needed' }}" @disabled(! $update->is_progress_update)>
                                                    </div>
                                                    <div class="col-md-4 col-lg-2">
                                                        <button class="btn btn-sm btn-outline-success w-100">Review</button>
                                                    </div>
                                                    <div class="col-12">
                                                        <textarea class="form-control form-control-sm" name="comments" rows="3" placeholder="Supervisor comments, verification notes, or revision guidance"></textarea>
                                                    </div>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-muted">No reports yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
    </div>

    <script>
        document.addEventListener('change', (event) => {
            const checkbox = event.target.closest('[name="is_progress_update"]');

            if (! checkbox) {
                return;
            }

            const form = checkbox.closest('form');
            const scoreInput = form?.querySelector('[data-progress-score]');

            if (! scoreInput) {
                return;
            }

            scoreInput.disabled = ! checkbox.checked;

            if (! checkbox.checked) {
                scoreInput.value = '';
            }
        });
    </script>

</x-app-layout>
