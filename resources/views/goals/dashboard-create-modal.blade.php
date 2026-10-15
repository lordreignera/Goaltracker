@php
    $dashboardObjectives = old('modal_source') === 'dashboard_goal'
        ? old('objectives', [])
        : [];
    $dashboardObjectives = $dashboardObjectives ?: [[
        'title' => '',
        'key_activities' => [''],
        'specific_output' => '',
        'weight' => '',
        'planned_weeks' => '',
        'reporting_frequency' => ['weekly'],
        'starts_at' => '',
        'due_at' => '',
    ]];
@endphp

<style>
    .dashboard-goal-row {
        border: 1px solid var(--sg-border);
        border-radius: 8px;
        background: var(--sg-surface-soft);
        padding: 14px;
    }

    .dashboard-activity-row {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 8px;
    }

    @media (max-width: 575.98px) {
        .dashboard-activity-row { grid-template-columns: 1fr; }
    }
</style>

<div class="modal fade" id="dashboardCreateGoalModal" tabindex="-1" aria-labelledby="dashboardCreateGoalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
        <form method="post" action="{{ route('goals.store') }}" class="modal-content" data-dashboard-goal-form data-weight-form>
            @csrf
            <input type="hidden" name="modal_source" value="dashboard_goal">

            <div class="modal-header">
                <div>
                    <h2 class="modal-title h5 fw-bold mb-1" id="dashboardCreateGoalModalLabel">Create Work Plan Goal</h2>
                    <div class="text-muted small">Add the pillar, departments, activities, dates, weight, and deliverable.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if ($errors->any() && old('modal_source') === 'dashboard_goal')
                    <x-validation-errors class="alert alert-danger mb-3" />
                @endif

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Quarter</label>
                        <select class="form-select" name="quarter_id" required data-dashboard-quarter>
                            <option value="">Select quarter</option>
                            @foreach ($quarters as $quarter)
                                <option value="{{ $quarter->id }}" data-start="{{ $quarter->starts_at->toDateString() }}" data-end="{{ $quarter->ends_at->toDateString() }}" @selected(old('quarter_id') == $quarter->id)>
                                    {{ $quarter->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Goal Pillar</label>
                        <select class="form-select" name="goal_pillar_id" required data-dashboard-pillar>
                            <option value="">Select goal pillar</option>
                            @foreach ($goalPillars as $goalPillar)
                                <option value="{{ $goalPillar->id }}" @selected(old('goal_pillar_id') == $goalPillar->id)>{{ $goalPillar->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        @include('goals.department-picker', [
                            'selectedDepartmentIds' => old('department_ids', auth()->user()->department_id ? [auth()->user()->department_id] : []),
                        ])
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                    <div>
                        <div class="fw-bold">Work Plan Goals, Key Activities & Deliverables</div>
                        <div class="text-muted small">All work plan goal weights must total exactly 100%.</div>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-dashboard-add-objective>Add Work Plan Goal</button>
                </div>

                @include('goals.weight-summary')

                <div class="d-grid gap-3" data-dashboard-objectives>
                    @foreach ($dashboardObjectives as $index => $objective)
                        @php
                            $activities = collect($objective['key_activities'] ?? [''])
                                ->map(fn ($activity) => trim((string) $activity))
                                ->filter()
                                ->values()
                                ->all() ?: [''];
                            $frequencies = (array) ($objective['reporting_frequency'] ?? ['weekly']);
                        @endphp
                        <section class="dashboard-goal-row" data-dashboard-objective>
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                <strong data-dashboard-objective-label>Work Plan Goal {{ $index + 1 }}</strong>
                                <button class="btn btn-sm btn-outline-danger" type="button" data-dashboard-remove-objective>Remove</button>
                            </div>

                            <div class="row g-3">
                                <div class="col-lg-4">
                                    <label class="form-label small fw-semibold">Goal Name</label>
                                    <input class="form-control" name="objectives[{{ $index }}][title]" value="{{ $objective['title'] ?? '' }}" placeholder="Goal name" required>
                                </div>
                                <div class="col-sm-6 col-lg-2">
                                    <label class="form-label small fw-semibold">Weight</label>
                                    <input class="form-control" type="number" min="1" max="100" name="objectives[{{ $index }}][weight]" value="{{ $objective['weight'] ?? '' }}" placeholder="Weight %" required>
                                </div>
                                <div class="col-sm-6 col-lg-2">
                                    <label class="form-label small fw-semibold">Duration</label>
                                    <select class="form-select" name="objectives[{{ $index }}][planned_weeks]" data-dashboard-weeks required>
                                        <option value="">Weeks</option>
                                        @for ($week = 1; $week <= 13; $week++)
                                            <option value="{{ $week }}" @selected(($objective['planned_weeks'] ?? '') == $week)>{{ $week }} week{{ $week === 1 ? '' : 's' }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-lg-4">
                                    <label class="form-label small fw-semibold d-block">Report Cadence</label>
                                    <div class="d-flex flex-wrap gap-3">
                                        @foreach (['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'] as $value => $label)
                                            <label class="form-check">
                                                <input class="form-check-input" type="checkbox" name="objectives[{{ $index }}][reporting_frequency][]" value="{{ $value }}" @checked(in_array($value, $frequencies, true))>
                                                <span class="form-check-label">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Start Date</label>
                                    <input class="form-control" type="date" name="objectives[{{ $index }}][starts_at]" value="{{ $objective['starts_at'] ?? '' }}" data-dashboard-start required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Due Date</label>
                                    <input class="form-control" type="date" name="objectives[{{ $index }}][due_at]" value="{{ $objective['due_at'] ?? '' }}" data-dashboard-due readonly required>
                                    <small class="text-muted">Calculated from the start date and duration.</small>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                        <label class="form-label small fw-semibold mb-0">Key Activities</label>
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-dashboard-add-activity>Add Activity</button>
                                    </div>
                                    <div class="d-grid gap-2" data-dashboard-activities>
                                        @foreach ($activities as $activityIndex => $activity)
                                            <div class="dashboard-activity-row" data-dashboard-activity>
                                                <input class="form-control" name="objectives[{{ $index }}][key_activities][{{ $activityIndex }}]" value="{{ $activity }}" placeholder="Key activity {{ $activityIndex + 1 }}" required>
                                                <button class="btn btn-outline-danger" type="button" data-dashboard-remove-activity>Remove</button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Deliverable</label>
                                    <textarea class="form-control" name="objectives[{{ $index }}][specific_output]" rows="3" placeholder="What result, deliverable, or evidence should be produced?" required>{{ $objective['specific_output'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </section>
                    @endforeach
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-outline-primary" name="save_action" value="draft" data-weight-submit>Save Draft</button>
                <button class="btn btn-primary" name="save_action" value="submit" data-weight-submit>Save Goal</button>
            </div>
        </form>
    </div>
</div>

<script>
    (() => {
        const form = document.querySelector('[data-dashboard-goal-form]');
        const list = form?.querySelector('[data-dashboard-objectives]');
        const quarter = form?.querySelector('[data-dashboard-quarter]');
        const pillar = form?.querySelector('[data-dashboard-pillar]');

        if (! form || ! list || ! quarter) {
            return;
        }

        function renumberActivities(row, objectiveIndex) {
            row.querySelectorAll('[data-dashboard-activity]').forEach((activityRow, activityIndex) => {
                const input = activityRow.querySelector('input');
                input.name = `objectives[${objectiveIndex}][key_activities][${activityIndex}]`;
                input.placeholder = `Key activity ${activityIndex + 1}`;
            });
        }

        function renumberObjectives() {
            list.querySelectorAll('[data-dashboard-objective]').forEach((row, objectiveIndex) => {
                row.querySelector('[data-dashboard-objective-label]').textContent = `Work Plan Goal ${objectiveIndex + 1}`;
                row.querySelectorAll('input, select, textarea').forEach((field) => {
                    field.name = field.name.replace(/objectives\[\d+\]/, `objectives[${objectiveIndex}]`);
                });
                renumberActivities(row, objectiveIndex);
            });
        }

        function selectedQuarterRange() {
            const option = quarter.selectedOptions[0];
            return { start: option?.dataset.start || '', end: option?.dataset.end || '' };
        }

        function formatDate(date) {
            return [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'), String(date.getDate()).padStart(2, '0')].join('-');
        }

        function updateDates(row) {
            const range = selectedQuarterRange();
            const start = row.querySelector('[data-dashboard-start]');
            const due = row.querySelector('[data-dashboard-due]');
            const weeks = row.querySelector('[data-dashboard-weeks]');

            start.min = range.start;
            start.max = range.end;
            due.min = range.start;
            due.max = range.end;

            if (! start.value || ! weeks.value) {
                due.value = '';
                return;
            }

            const dueDate = new Date(`${start.value}T00:00:00`);
            dueDate.setDate(dueDate.getDate() + (Number(weeks.value) * 7) - 1);
            const calculated = formatDate(dueDate);
            due.value = range.end && calculated > range.end ? '' : calculated;
        }

        function resetObjective(row) {
            row.querySelectorAll('[data-dashboard-activity]').forEach((activity, index) => {
                if (index > 0) activity.remove();
            });
            row.querySelectorAll('input, select, textarea').forEach((field) => {
                if (field.type === 'checkbox') {
                    field.checked = field.value === 'weekly';
                } else {
                    field.value = '';
                }
            });
        }

        form.addEventListener('click', (event) => {
            if (event.target.matches('[data-dashboard-add-objective]')) {
                const row = list.querySelector('[data-dashboard-objective]').cloneNode(true);
                resetObjective(row);
                list.appendChild(row);
                renumberObjectives();
                updateDates(row);
                return;
            }

            if (event.target.matches('[data-dashboard-remove-objective]')) {
                if (list.querySelectorAll('[data-dashboard-objective]').length > 1) {
                    event.target.closest('[data-dashboard-objective]').remove();
                    renumberObjectives();
                }
                return;
            }

            if (event.target.matches('[data-dashboard-add-activity]')) {
                const row = event.target.closest('[data-dashboard-objective]');
                const activities = row.querySelector('[data-dashboard-activities]');
                const activity = activities.querySelector('[data-dashboard-activity]').cloneNode(true);
                activity.querySelector('input').value = '';
                activities.appendChild(activity);
                renumberObjectives();
                return;
            }

            if (event.target.matches('[data-dashboard-remove-activity]')) {
                const row = event.target.closest('[data-dashboard-objective]');
                if (row.querySelectorAll('[data-dashboard-activity]').length > 1) {
                    event.target.closest('[data-dashboard-activity]').remove();
                    renumberObjectives();
                }
            }
        });

        form.addEventListener('change', (event) => {
            if (event.target.matches('[data-dashboard-quarter]')) {
                list.querySelectorAll('[data-dashboard-objective]').forEach(updateDates);
            }

            if (event.target.matches('[data-dashboard-start], [data-dashboard-weeks]')) {
                updateDates(event.target.closest('[data-dashboard-objective]'));
            }
        });

        list.querySelectorAll('[data-dashboard-objective]').forEach(updateDates);

        document.querySelectorAll('[data-dashboard-add-for-pillar]').forEach((button) => {
            button.addEventListener('click', () => {
                if (pillar) {
                    pillar.value = button.dataset.pillarId || '';
                }
            });
        });

        @if ($errors->any() && old('modal_source') === 'dashboard_goal')
            document.addEventListener('DOMContentLoaded', () => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('dashboardCreateGoalModal')).show();
            });
        @endif
    })();
</script>
