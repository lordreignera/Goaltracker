<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">{{ $goal->assignedDepartments->pluck('name')->join(', ') ?: 'Office' }} Work Plan</h1>
    </x-slot>

    <style>
        .goal-summary-actions { min-width: 220px; }
        .goal-action-stack { display: grid; gap: 8px; justify-items: end; }
        .report-row, .goal-objective { scroll-margin-top: 110px; }
        .report-row:target { outline: 2px solid #2563eb; outline-offset: -2px; }
        .report-summary { max-width: 340px; }

        @media (max-width: 767.98px) {
            .goal-summary { flex-direction: column; }
            .goal-summary-actions { width: 100%; min-width: 0; text-align: left !important; }
            .goal-action-stack { justify-items: stretch; }
        }
    </style>

    <x-validation-errors class="alert alert-danger mb-3" />

    <div class="d-grid gap-4">
        <section class="bg-white border rounded-3 p-4">
            <div class="goal-summary d-flex justify-content-between gap-3">
                <div class="flex-grow-1">
                    <div class="text-muted">
                        {{ $goal->quarter->name }} /
                        {{ $goal->pillar?->name ?? 'Unassigned pillar' }} /
                        {{ $goal->assignedDepartments->pluck('name')->unique()->join(', ') }}
                    </div>
                    <div class="mt-3">
                        <strong>Status:</strong>
                        <span class="badge text-bg-{{ in_array($goal->status, ['draft', 'submitted'], true) ? 'warning' : 'success' }}">
                            {{ $goal->status === 'submitted' ? 'Submitted for review' : str_replace('_', ' ', ucfirst($goal->status)) }}
                        </span>
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
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('goals.edit', $goal) }}">Edit Work Plan</a>
                            @if ($goal->status === 'draft')
                                <form method="post" action="{{ route('goals.submit', $goal) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success w-100">Submit Goal</button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section>

        @foreach ($goal->objectives as $objective)
            @php
                $objectiveIsApprovedComplete = $objective->isApprovedComplete();
                $objectiveStatusLabel = $objectiveIsApprovedComplete ? 'completed' : $objective->status;
                $achievementProgress = $objective->progressPercent();
                $objectiveContribution = round($objective->progressContribution(), 1);
                $reportingFrequencies = $objective->reportingFrequencies();
            @endphp

            <section class="goal-objective bg-white border rounded-3 p-4" id="report-objective-{{ $objective->id }}">
                <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                    <div>
                        <h2 class="h5 mb-1">{{ $objective->title }}</h2>
                        <div class="text-muted small">
                            {{ $objective->planned_weeks }} planned week{{ $objective->planned_weeks === 1 ? '' : 's' }}
                            / {{ $objective->starts_at?->format('M d, Y') }} - {{ $objective->due_at?->format('M d, Y') }}
                            / {{ collect($reportingFrequencies)->map(fn ($frequency) => ucfirst($frequency))->join(', ') }} reporting
                        </div>
                        <div class="mt-2">
                            <strong>Key Activities:</strong>
                            <ul class="mb-0 mt-1">
                                @foreach ($objective->keyActivitiesList() as $activity)
                                    <li>{{ $activity }}</li>
                                @endforeach
                            </ul>
                            <div class="mt-1"><strong>Deliverable:</strong> {{ $objective->specific_output }}</div>
                        </div>
                    </div>
                    <span class="badge text-bg-{{ $objectiveIsApprovedComplete ? 'success' : 'secondary' }} align-self-start">
                        {{ str_replace('_', ' ', $objectiveStatusLabel) }} / {{ $objectiveContribution }}% of {{ $objective->weight }}%
                    </span>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>{{ $achievementProgress }}% latest supervisor-verified achievement across {{ $objective->totalReportingWeeks() }} planned weeks</span>
                        <span>{{ $achievementProgress }}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" style="width: {{ $achievementProgress }}%"></div>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <div>
                        <h3 class="h6 mb-0">Reports <span class="text-muted">({{ $objective->weeklyUpdates->count() }})</span></h3>
                        <div class="small text-muted">Supervisor-approved reports update verified progress.</div>
                    </div>
                    @if ($canSubmitReport)
                        <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addReportModal-{{ $objective->id }}">
                            {{ $objective->weeklyUpdates->isEmpty() ? 'Add Report' : 'Add Another Report' }}
                        </button>
                    @endif
                </div>

                @if (session('report_deleted_objective_id') == $objective->id)
                    <div class="alert alert-success py-2 small" role="status">Report deleted. You can add another report for this period.</div>
                @endif

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Reporting Period</th>
                                <th>Cadence</th>
                                <th>Reported By</th>
                                <th>Achievement</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($objective->weeklyUpdates->sortByDesc('created_at') as $update)
                                @php
                                    $reportStatus = match ($update->status) {
                                        'submitted' => 'Awaiting approval',
                                        'approved' => 'Approved',
                                        'rejected' => 'Rejected',
                                        'revision_requested' => 'Revision requested',
                                        default => ucfirst(str_replace('_', ' ', $update->status)),
                                    };
                                    $statusColor = match ($update->status) {
                                        'submitted' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        default => 'secondary',
                                    };
                                    $canEditUpdate = $update->user_id === auth()->id() && $update->status !== 'approved' && $canSubmitReport;
                                    $canReviewUpdate = $canReviewGoal && $update->status === 'submitted' && $update->user_id !== auth()->id();
                                    $latestReview = $update->reviews->sortByDesc('created_at')->first();
                                    $verifiedPercentage = $update->verifiedAchievementPercent();
                                @endphp
                                <tr class="report-row @if (session('report_notice_id') == $update->id) table-success @endif" id="report-{{ $update->id }}">
                                    <td>
                                        {{ $update->report_period_start?->format('M d, Y') }} - {{ $update->report_period_end?->format('M d, Y') }}
                                        @if (session('report_notice_id') == $update->id)
                                            <span class="d-block small text-success fw-semibold">Saved successfully. Awaiting approval.</span>
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($update->reporting_frequency ?? 'weekly') }}</td>
                                    <td>{{ $update->user?->name ?? 'Former user' }}</td>
                                    <td class="report-summary">{{ \Illuminate\Support\Str::limit($update->achievement_summary, 90) }}</td>
                                    <td>
                                        @if ($update->is_progress_update)
                                            {{ $update->achievement_percentage }}% claimed
                                            <span class="d-block small text-muted">{{ $verifiedPercentage !== null ? $verifiedPercentage.'% verified' : 'Not verified yet' }}</span>
                                        @else
                                            <span class="text-muted">Activity update</span>
                                        @endif
                                    </td>
                                    <td><span class="badge text-bg-{{ $statusColor }}">{{ $reportStatus }}</span></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#viewReportModal-{{ $update->id }}">View</button>
                                            @if ($canEditUpdate)
                                                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#editReportModal-{{ $update->id }}">Edit</button>
                                                <form method="post" action="{{ route('weekly-updates.destroy', $update) }}" onsubmit="return confirm('Delete this report? This cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                                </form>
                                            @endif
                                            @if ($canReviewUpdate)
                                                <button class="btn btn-sm btn-outline-success" type="button" data-bs-toggle="modal" data-bs-target="#reviewReportModal-{{ $update->id }}">Review</button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-muted py-3">
                                        {{ $canSubmitReport ? 'No reports yet. Add the first report for this work plan goal.' : 'No reports yet.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            @if ($canSubmitReport)
                <div class="modal fade" id="addReportModal-{{ $objective->id }}" tabindex="-1" aria-labelledby="addReportLabel-{{ $objective->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
                        <form method="post" action="{{ route('objectives.weekly-updates.store', $objective) }}" class="modal-content" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="report_objective_id" value="{{ $objective->id }}">
                            <div class="modal-header">
                                <div>
                                    <h3 class="modal-title h5 mb-1" id="addReportLabel-{{ $objective->id }}">Add Report</h3>
                                    <div class="text-muted small">{{ $objective->title }}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    @include('goals.partials.report-period-fields', [
                                        'objective' => $objective,
                                        'useOldInput' => old('report_objective_id') == $objective->id,
                                    ])
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Report Cadence</label>
                                        <select class="form-select" name="reporting_frequency" data-report-frequency required>
                                            @foreach ($reportingFrequencies as $frequency)
                                                <option value="{{ $frequency }}" @selected(old('report_objective_id') == $objective->id && old('reporting_frequency') === $frequency)>{{ ucfirst($frequency) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold d-block">Progress Score</label>
                                        <label class="form-check mb-1">
                                            <input class="form-check-input" type="checkbox" name="is_progress_update" value="1" @checked(old('is_progress_update'))>
                                            <span class="form-check-label small">This report updates progress</span>
                                        </label>
                                        <input class="form-control" type="number" name="achievement_percentage" min="0" max="100" value="{{ old('achievement_percentage') }}" placeholder="Achievement %" data-progress-score @disabled(! old('is_progress_update'))>
                                        <small class="text-muted">Required only when updating progress.</small>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Achievement</label>
                                        <textarea class="form-control" name="achievement_summary" rows="3" required>{{ old('achievement_summary') }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Challenges</label>
                                        <textarea class="form-control" name="challenges" rows="2">{{ old('challenges') }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Action Point / Next Step</label>
                                        <textarea class="form-control" name="action_points" rows="2">{{ old('action_points') }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Evidence Document</label>
                                        <input class="form-control" type="file" name="evidence_file" accept=".pdf,.doc,.docx,.docm,.xls,.xlsx,.xlsm,.csv,.png,.jpg,.jpeg,.webp,.heic,.heif">
                                        <small class="text-muted">Optional PDF, Word, Excel, CSV, or image file. Max 10MB.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-primary" type="submit">Submit Report</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @foreach ($objective->weeklyUpdates as $update)
                @php
                    $canEditUpdate = $update->user_id === auth()->id() && $update->status !== 'approved' && $canSubmitReport;
                    $canReviewUpdate = $canReviewGoal && $update->status === 'submitted' && $update->user_id !== auth()->id();
                    $latestReview = $update->reviews->sortByDesc('created_at')->first();
                @endphp

                <div class="modal fade" id="viewReportModal-{{ $update->id }}" tabindex="-1" aria-labelledby="viewReportLabel-{{ $update->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div>
                                    <h3 class="modal-title h5 mb-1" id="viewReportLabel-{{ $update->id }}">Report for {{ $objective->title }}</h3>
                                    <div class="text-muted small">{{ $update->report_period_start?->format('M d, Y') }} - {{ $update->report_period_end?->format('M d, Y') }}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Reported by</dt><dd class="col-sm-8">{{ $update->user?->name ?? 'Former user' }}</dd>
                                    <dt class="col-sm-4">Status</dt><dd class="col-sm-8">{{ match ($update->status) { 'submitted' => 'Awaiting approval', 'revision_requested' => 'Revision requested', default => ucfirst($update->status) } }}</dd>
                                    <dt class="col-sm-4">Achievement</dt><dd class="col-sm-8">{{ $update->achievement_summary }}</dd>
                                    <dt class="col-sm-4">Challenges</dt><dd class="col-sm-8">{{ $update->challenges ?: 'None reported' }}</dd>
                                    <dt class="col-sm-4">Next step</dt><dd class="col-sm-8">{{ $update->action_points ?: 'None reported' }}</dd>
                                    <dt class="col-sm-4">Progress</dt><dd class="col-sm-8">{{ $update->is_progress_update ? $update->achievement_percentage.'% claimed' : 'Activity update' }}@if ($update->verifiedAchievementPercent() !== null) / {{ $update->verifiedAchievementPercent() }}% verified @endif</dd>
                                    <dt class="col-sm-4">Evidence</dt>
                                    <dd class="col-sm-8">
                                        @if ($update->hasEvidence())
                                            <a href="{{ route('weekly-updates.evidence', $update) }}">{{ $update->evidence_original_name ?? 'Download evidence' }}</a>
                                        @else
                                            None attached
                                        @endif
                                    </dd>
                                    @if ($latestReview)
                                        <dt class="col-sm-4">Supervisor review</dt><dd class="col-sm-8">{{ $latestReview->comments ?: ucfirst(str_replace('_', ' ', $latestReview->decision)) }}</dd>
                                    @endif
                                </dl>
                            </div>
                            <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button></div>
                        </div>
                    </div>
                </div>

                @if ($canEditUpdate)
                    <div class="modal fade" id="editReportModal-{{ $update->id }}" tabindex="-1" aria-labelledby="editReportLabel-{{ $update->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
                            <form method="post" action="{{ route('weekly-updates.update', $update) }}" class="modal-content" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="editing_report_id" value="{{ $update->id }}">
                                <div class="modal-header">
                                    <h3 class="modal-title h5" id="editReportLabel-{{ $update->id }}">Edit Report</h3>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        @include('goals.partials.report-period-fields', [
                                            'objective' => $objective,
                                            'selectedPeriod' => $update->report_period_start?->toDateString() ?: $update->report_date?->toDateString(),
                                            'selectedFrequency' => $update->reporting_frequency,
                                            'useOldInput' => old('editing_report_id') == $update->id,
                                        ])
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Report Cadence</label>
                                            <select class="form-select" name="reporting_frequency" data-report-frequency required>
                                                @foreach ($reportingFrequencies as $frequency)
                                                    <option value="{{ $frequency }}" @selected(old('editing_report_id') == $update->id ? old('reporting_frequency', $update->reporting_frequency) === $frequency : $update->reporting_frequency === $frequency)>{{ ucfirst($frequency) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold d-block">Progress Score</label>
                                            <label class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" name="is_progress_update" value="1" @checked(old('editing_report_id') == $update->id ? old('is_progress_update', $update->is_progress_update) : $update->is_progress_update)>
                                                <span class="form-check-label small">This report updates progress</span>
                                            </label>
                                            <input class="form-control" type="number" name="achievement_percentage" min="0" max="100" value="{{ old('editing_report_id') == $update->id ? old('achievement_percentage', $update->achievement_percentage) : $update->achievement_percentage }}" placeholder="Achievement %" data-progress-score @disabled(! (old('editing_report_id') == $update->id ? old('is_progress_update', $update->is_progress_update) : $update->is_progress_update))>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-semibold">Achievement</label>
                                            <textarea class="form-control" name="achievement_summary" rows="3" required>{{ old('editing_report_id') == $update->id ? old('achievement_summary', $update->achievement_summary) : $update->achievement_summary }}</textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Challenges</label>
                                            <textarea class="form-control" name="challenges" rows="2">{{ old('editing_report_id') == $update->id ? old('challenges', $update->challenges) : $update->challenges }}</textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Action Point / Next Step</label>
                                            <textarea class="form-control" name="action_points" rows="2">{{ old('editing_report_id') == $update->id ? old('action_points', $update->action_points) : $update->action_points }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-semibold">Replace Evidence Document</label>
                                            <input class="form-control" type="file" name="evidence_file" accept=".pdf,.doc,.docx,.docm,.xls,.xlsx,.xlsm,.csv,.png,.jpg,.jpeg,.webp,.heic,.heif">
                                            @if ($update->hasEvidence())<small class="text-muted">Current: {{ $update->evidence_original_name ?? 'Evidence uploaded' }}</small>@endif
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                                    <button class="btn btn-primary" type="submit">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                @if ($canReviewUpdate)
                    <div class="modal fade" id="reviewReportModal-{{ $update->id }}" tabindex="-1" aria-labelledby="reviewReportLabel-{{ $update->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
                            <form method="post" action="{{ route('weekly-updates.reviews.store', $update) }}" class="modal-content">
                                @csrf
                                <div class="modal-header">
                                    <h3 class="modal-title h5" id="reviewReportLabel-{{ $update->id }}">Review Report</h3>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="mb-3">{{ $update->achievement_summary }}</p>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Decision</label>
                                            <select class="form-select" name="decision" required>
                                                <option value="approved">Approve</option>
                                                <option value="rejected">Reject</option>
                                                <option value="revision_requested">Request revision</option>
                                            </select>
                                        </div>
                                        @if ($update->is_progress_update)
                                            <div class="col-md-6">
                                                <label class="form-label small fw-semibold">Verified Achievement %</label>
                                                <input class="form-control" type="number" name="verified_percentage" min="0" max="100" value="{{ $update->achievement_percentage }}">
                                            </div>
                                        @endif
                                        <div class="col-12">
                                            <label class="form-label small fw-semibold">Supervisor Comments</label>
                                            <textarea class="form-control" name="comments" rows="3" placeholder="Verification notes or revision guidance"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                                    <button class="btn btn-primary" type="submit">Save Review</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            @endforeach
        @endforeach
    </div>

    <script>
        document.addEventListener('change', (event) => {
            const checkbox = event.target.closest('[name="is_progress_update"]');
            if (! checkbox) return;
            const scoreInput = checkbox.closest('form')?.querySelector('[data-progress-score]');
            if (! scoreInput) return;
            scoreInput.disabled = ! checkbox.checked;
            if (! checkbox.checked) scoreInput.value = '';
        });

        document.addEventListener('DOMContentLoaded', () => {
            const requestedObjective = location.hash.match(/^#add-report-objective-(\d+)$/)?.[1];
            const failedObjective = @json($errors->any() ? old('report_objective_id') : null);
            const failedEdit = @json($errors->any() ? old('editing_report_id') : null);
            const modalId = failedEdit ? `editReportModal-${failedEdit}` :
                failedObjective ? `addReportModal-${failedObjective}` :
                requestedObjective ? `addReportModal-${requestedObjective}` : null;
            const modal = modalId ? document.getElementById(modalId) : null;
            if (modal && window.bootstrap?.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(modal).show();
            }
        });
    </script>
</x-app-layout>
