<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">{{ $goal->title }}</h1>
    </x-slot>

    <style>
        .goal-summary-actions {
            min-width: 180px;
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

            .review-form {
                align-items: stretch;
            }
        }

        .report-list-row {
            display: flex;
            gap: .5rem;
            align-items: start;
        }

        .report-list-row textarea {
            min-height: 58px;
        }

        .weekly-report-form {
            background: #fbfcfd;
        }

        .report-section {
            border: 1px solid #e7ebf0;
            border-radius: 12px;
            padding: 14px;
            background: #fff;
        }

        .report-section .report-list-row {
            max-width: 100%;
        }
    </style>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="bg-white border rounded-3 p-4 mb-4">
                <div class="goal-summary d-flex justify-content-between gap-3">
                    <div>
                        <div class="text-muted">
                            {{ $goal->quarter->name }} /
                            {{ $goal->assignedDepartments->pluck('name')->join(', ') ?: $goal->department->name }} /
                            {{ $goal->assignedUnits->isNotEmpty() ? $goal->assignedUnits->pluck('name')->join(', ') : 'Department-wide' }}
                        </div>
                        <p class="mb-0 mt-2">{{ $goal->description }}</p>
                    </div>
                    <div class="goal-summary-actions text-end">
                        <div class="h2 mb-1">{{ $goal->progress() }}%</div>
                        <small class="text-muted d-block mb-2">Completed approved objective weight</small>
                        <div class="progress"><div class="progress-bar bg-success" style="width: {{ $goal->progress() }}%"></div></div>
                        @if ($canUpdateGoal)
                        <a class="btn btn-sm btn-outline-secondary mt-3" href="{{ route('goals.edit', $goal) }}">Edit Goal</a>
                        <form method="post" action="{{ route('goals.submit', $goal) }}" class="mt-3">
                            @csrf
                            <button class="btn btn-sm btn-outline-success">Submit Goal</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>

            @foreach ($goal->objectives as $objective)
                @php
                    $objectiveIsApprovedComplete = $objective->isApprovedComplete();
                    $objectiveStatusLabel = $objectiveIsApprovedComplete ? 'completed' : $objective->status;
                    $approvedWeeks = $objective->approvedReportingWeeksCount();
                    $totalWeeks = $objective->totalReportingWeeks();
                    $weeklyProgress = round($objective->weeklyProgressPercent(), 1);
                    $objectiveContribution = round($objective->progressContribution(), 1);
                    [$firstReportDate, $lastReportDate] = $objective->reportingDateRange();
                    $quarterStart = $firstReportDate?->toDateString();
                    $maxReportDate = $lastReportDate?->toDateString();
                    $weeklyDateOptions = $objective->reportingWeekOptions();
                @endphp
                <div class="bg-white border rounded-3 p-4 mb-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                        <div>
                            <h2 class="h5 mb-1">{{ $objective->title }}</h2>
                            <p class="text-muted mb-0">{{ $objective->description }}</p>
                        </div>
                        <span class="badge text-bg-{{ $objectiveIsApprovedComplete ? 'success' : 'secondary' }} align-self-start">
                            {{ str_replace('_', ' ', $objectiveStatusLabel) }} / {{ $objectiveContribution }}% of {{ $objective->weight }}%
                        </span>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>{{ $approvedWeeks }} of {{ $totalWeeks }} planned weekly reports approved</span>
                            <span>{{ $weeklyProgress }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: {{ $weeklyProgress }}%"></div>
                        </div>
                    </div>

                    <form method="post" action="{{ route('objectives.weekly-updates.store', $objective) }}" class="weekly-report-form border rounded-3 p-3 p-md-4 mb-3">
                        @csrf
                        @if ($errors->any())
                            <div class="alert alert-danger py-2 small">
                                Please check the report fields. Dates must stay within the quarter and objective timeline.
                            </div>
                        @endif
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Week</label>
                                <select class="form-select" name="week_number" data-week-select required>
                                    <option value="">Select week</option>
                                    @foreach ($weeklyDateOptions as $option)
                                        <option value="{{ $option['week'] }}" data-week-date="{{ $option['date'] }}">{{ $option['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Week starting</label>
                                <input class="form-control" type="date" name="week_starting" min="{{ $quarterStart }}" max="{{ $maxReportDate }}" readonly required>
                                <small class="text-muted">{{ count($weeklyDateOptions) }} planned reporting week{{ count($weeklyDateOptions) === 1 ? '' : 's' }} for this objective.</small>
                            </div>
                            <div class="col-12"><textarea class="form-control" name="progress_summary" placeholder="Progress summary" required></textarea></div>
                            <div class="col-12">
                                <div class="report-section h-100">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-semibold mb-0">Achievements</label>
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-add-report-item="achievements">Add</button>
                                </div>
                                <div class="d-grid gap-2" data-report-list="achievements">
                                    <div class="report-list-row">
                                        <textarea class="form-control" name="achievements[]" placeholder="Add one achievement"></textarea>
                                        <button class="btn btn-outline-danger" type="button" data-remove-report-item>&times;</button>
                                    </div>
                                </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="report-section h-100">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-semibold mb-0">Challenges</label>
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-add-report-item="challenges">Add</button>
                                </div>
                                <div class="d-grid gap-2" data-report-list="challenges">
                                    <div class="report-list-row">
                                        <textarea class="form-control" name="challenges[]" placeholder="Add one challenge"></textarea>
                                        <button class="btn btn-outline-danger" type="button" data-remove-report-item>&times;</button>
                                    </div>
                                </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="report-section h-100">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-semibold mb-0">Recommendations / Next Actions</label>
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-add-report-item="next_actions">Add</button>
                                </div>
                                <div class="d-grid gap-2" data-report-list="next_actions">
                                    <div class="report-list-row">
                                        <textarea class="form-control" name="next_actions[]" placeholder="Add one recommendation"></textarea>
                                        <button class="btn btn-outline-danger" type="button" data-remove-report-item>&times;</button>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-primary mt-3">Submit Weekly Update</button>
                    </form>

                    @foreach ($objective->weeklyUpdates as $update)
                        @php
                            $canEditUpdate = $update->user_id === auth()->id() && $update->status !== 'approved';
                            $achievementItems = preg_split('/\r\n|\r|\n/', (string) $update->achievements, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                            $challengeItems = preg_split('/\r\n|\r|\n/', (string) $update->challenges, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                            $nextActionItems = preg_split('/\r\n|\r|\n/', (string) $update->next_actions, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                        @endphp
                        <div class="border-top py-3">
                            <div class="d-flex justify-content-between">
                                <strong>Week {{ $update->week_number }} @if($update->week_starting) / {{ $update->week_starting->format('M d, Y') }} @endif</strong>
                                <span class="badge text-bg-light border">{{ str_replace('_', ' ', $update->status) }}</span>
                            </div>
                            <p class="mb-2">{{ $update->progress_summary }}</p>
                            <div class="row small text-muted">
                                <div class="col-md-4">
                                    <strong>Achievements:</strong>
                                    @forelse ($achievementItems as $achievement)
                                        <div>&bull; {{ $achievement }}</div>
                                    @empty
                                        <div>None provided</div>
                                    @endforelse
                                </div>
                                <div class="col-md-4">
                                    <strong>Challenges:</strong>
                                    @forelse ($challengeItems as $challenge)
                                        <div>&bull; {{ $challenge }}</div>
                                    @empty
                                        <div>None provided</div>
                                    @endforelse
                                </div>
                                <div class="col-md-4">
                                    <strong>Recommendations:</strong>
                                    @forelse ($nextActionItems as $nextAction)
                                        <div>&bull; {{ $nextAction }}</div>
                                    @empty
                                        <div>None provided</div>
                                    @endforelse
                                </div>
                            </div>
                            @if ($canEditUpdate)
                                <details class="mt-3">
                                    <summary class="btn btn-sm btn-outline-secondary">Edit Submission</summary>
                                    <form method="post" action="{{ route('weekly-updates.update', $update) }}" class="border rounded-3 p-3 mt-3">
                                        @csrf
                                        @method('PUT')
                                        <div class="row g-2">
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold">Week</label>
                                                <select class="form-select" name="week_number" data-week-select required>
                                                    @foreach ($weeklyDateOptions as $option)
                                                        <option value="{{ $option['week'] }}" data-week-date="{{ $option['date'] }}" @selected(old('week_number', $update->week_number) == $option['week'])>{{ $option['label'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-semibold">Week starting</label>
                                                <input class="form-control" type="date" name="week_starting" min="{{ $quarterStart }}" max="{{ $maxReportDate }}" value="{{ old('week_starting', $update->week_starting?->toDateString()) }}" readonly required>
                                                <small class="text-muted">{{ count($weeklyDateOptions) }} planned reporting week{{ count($weeklyDateOptions) === 1 ? '' : 's' }} for this objective.</small>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small fw-semibold">Progress summary</label>
                                                <textarea class="form-control" name="progress_summary" required>{{ old('progress_summary', $update->progress_summary) }}</textarea>
                                            </div>
                                            <div class="col-12">
                                                <div class="report-section h-100">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label small fw-semibold mb-0">Achievements</label>
                                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-add-report-item="achievements">Add</button>
                                                </div>
                                                <div class="d-grid gap-2" data-report-list="achievements">
                                                    @foreach (($achievementItems ?: ['']) as $achievement)
                                                        <div class="report-list-row">
                                                            <textarea class="form-control" name="achievements[]" placeholder="Add one achievement">{{ $achievement }}</textarea>
                                                            <button class="btn btn-outline-danger" type="button" data-remove-report-item>&times;</button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="report-section h-100">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label small fw-semibold mb-0">Challenges</label>
                                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-add-report-item="challenges">Add</button>
                                                </div>
                                                <div class="d-grid gap-2" data-report-list="challenges">
                                                    @foreach (($challengeItems ?: ['']) as $challenge)
                                                        <div class="report-list-row">
                                                            <textarea class="form-control" name="challenges[]" placeholder="Add one challenge">{{ $challenge }}</textarea>
                                                            <button class="btn btn-outline-danger" type="button" data-remove-report-item>&times;</button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="report-section h-100">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label small fw-semibold mb-0">Recommendations / Next Actions</label>
                                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-add-report-item="next_actions">Add</button>
                                                </div>
                                                <div class="d-grid gap-2" data-report-list="next_actions">
                                                    @foreach (($nextActionItems ?: ['']) as $nextAction)
                                                        <div class="report-list-row">
                                                            <textarea class="form-control" name="next_actions[]" placeholder="Add one recommendation">{{ $nextAction }}</textarea>
                                                            <button class="btn btn-outline-danger" type="button" data-remove-report-item>&times;</button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button class="btn btn-sm btn-primary mt-3">Save Changes</button>
                                    </form>
                                </details>
                            @endif
                            @if ($canReviewGoal)
                            <form method="post" action="{{ route('weekly-updates.reviews.store', $update) }}" class="review-form row g-2 mt-2">
                                @csrf
                                <div class="col-md-3">
                                    <select class="form-select form-select-sm" name="decision" required>
                                        <option value="approved">Approve as completed</option>
                                        <option value="rejected">Reject</option>
                                        <option value="revision_requested">Request revision</option>
                                    </select>
                                </div>
                                <div class="col-md-7"><input class="form-control form-control-sm" name="comments" placeholder="Supervisor comments"></div>
                                <div class="col-md-2"><button class="btn btn-sm btn-outline-success w-100">Review</button></div>
                            </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        @if ($canUpdateGoal)
            <div class="col-xl-4">
                <div class="bg-white border rounded-3 p-4">
                    <h2 class="h5 fw-bold">Edit Objectives</h2>
                    <p class="text-muted small">To add, remove, or change objective weights, edit the main goal. The objective weights must still total 100%.</p>
                    <a class="btn btn-outline-secondary w-100" href="{{ route('goals.edit', $goal) }}">Edit Goal & Objectives</a>
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('click', (event) => {
            const addButton = event.target.closest('[data-add-report-item]');
            if (addButton) {
                const field = addButton.dataset.addReportItem;
                const list = addButton.closest('.report-section').querySelector(`[data-report-list="${field}"]`);
                const row = document.createElement('div');
                row.className = 'report-list-row';
                row.innerHTML = `
                    <textarea class="form-control" name="${field}[]" placeholder="Add one item"></textarea>
                    <button class="btn btn-outline-danger" type="button" data-remove-report-item>&times;</button>
                `;
                list.appendChild(row);
                row.querySelector('textarea').focus();
                return;
            }

            const removeButton = event.target.closest('[data-remove-report-item]');
            if (! removeButton) {
                return;
            }

            const list = removeButton.closest('[data-report-list]');
            if (list.querySelectorAll('.report-list-row').length === 1) {
                removeButton.closest('.report-list-row').querySelector('textarea').value = '';
                return;
            }

            removeButton.closest('.report-list-row').remove();
        });

        document.addEventListener('change', (event) => {
            const weekSelect = event.target.closest('[data-week-select]');
            if (! weekSelect) {
                return;
            }

            const dateInput = weekSelect.closest('form').querySelector('[name="week_starting"]');
            const selectedOption = weekSelect.selectedOptions[0];

            if (dateInput) {
                dateInput.value = selectedOption?.dataset.weekDate || '';
            }
        });
    </script>
</x-app-layout>
