<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Create Goal</h1>
    </x-slot>

    <style>
        .goal-panel {
            border: 1px solid #e6e9ef;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 10px 28px rgba(20, 24, 31, .04);
        }

        .btn-maroon {
            background: var(--arm-maroon);
            border-color: var(--arm-maroon);
            color: #fff;
            font-weight: 800;
        }

        .btn-maroon:hover {
            background: var(--arm-maroon-dark);
            border-color: var(--arm-maroon-dark);
            color: #fff;
        }

        .objective-row {
            border: 1px solid #e6e9ef;
            border-radius: 12px;
            background: #fbfcfd;
            padding: 14px;
        }

        .field-hint {
            color: #6b7280;
            font-size: .82rem;
            margin-top: .35rem;
        }

        .checkbox-dropdown {
            position: relative;
        }

        .checkbox-dropdown-toggle {
            min-height: 42px;
            background-color: #fff;
        }

        .checkbox-dropdown-menu {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            z-index: 20;
            max-height: 260px;
            overflow-y: auto;
            border: 1px solid #d9dee7;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 14px 34px rgba(20, 24, 31, .12);
            padding: 8px;
        }

        .checkbox-dropdown-option {
            display: flex;
            align-items: center;
            gap: 8px;
            border-radius: 6px;
            padding: 8px;
            cursor: pointer;
        }

        .checkbox-dropdown-option:hover {
            background: #f4f6fb;
        }
    </style>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
        <div>
            <h2 class="h5 fw-bold mb-1">Create Main Goal</h2>
            <div class="text-muted small">Set the main SMART goal first, then break it into measurable objectives below.</div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('goals.index') }}">Back to Goals</a>
    </div>

    <x-validation-errors class="alert alert-danger mb-3" />

    <form method="post" action="{{ route('goals.store') }}" class="goal-panel p-4">
        @csrf

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Quarter</label>
                <select class="form-select" name="quarter_id" required>
                    <option value="">Select quarter</option>
                    @foreach ($quarters as $quarter)
                        <option value="{{ $quarter->id }}" data-start="{{ $quarter->starts_at->toDateString() }}" data-end="{{ $quarter->ends_at->toDateString() }}" @selected(old('quarter_id') == $quarter->id)>
                            {{ $quarter->name }} ({{ $quarter->starts_at->format('M d, Y') }} - {{ $quarter->ends_at->format('M d, Y') }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Departments</label>
                <div class="checkbox-dropdown" data-department-dropdown>
                    <button class="form-select text-start checkbox-dropdown-toggle" type="button" data-department-toggle aria-expanded="false">
                        <span data-department-summary>Select departments</span>
                    </button>

                    <div class="checkbox-dropdown-menu d-none" data-department-menu>
                        @foreach ($departments as $department)
                            <label class="checkbox-dropdown-option">
                                <input
                                    class="form-check-input mt-0"
                                    type="checkbox"
                                    name="department_ids[]"
                                    value="{{ $department->id }}"
                                    data-department-checkbox
                                    data-department-name="{{ $department->name }}"
                                    @checked(in_array($department->id, old('department_ids', [])))>
                                <span>{{ $department->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <small class="text-muted">Choose one or more departments.</small>
            </div>
            <div class="col-md-4" data-section-scope>
                <label class="form-label fw-semibold">Sections</label>
                <select class="form-select mb-2" name="section_ids[]" multiple size="5" data-section-select>
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}" data-department-id="{{ $section->department_id }}" @selected(in_array($section->id, old('section_ids', [])))>{{ $section->department->name ?? 'Department' }} - {{ $section->name }}</option>
                    @endforeach
                </select>
                <small class="text-muted d-block mb-2">Select sections for section-wide goals.</small>
            </div>

            <div class="col-md-4" data-unit-scope>
                <label class="form-label fw-semibold">Lower Units</label>
                <select class="form-select" name="unit_ids[]" multiple size="5" data-unit-select>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" data-department-id="{{ $unit->department_id }}" data-section-id="{{ $unit->section_id }}" @selected(in_array($unit->id, old('unit_ids', [])))>{{ $unit->department->name ?? 'Department' }} - {{ $unit->section->name ?? 'Section' }} - {{ $unit->name }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Select units for unit or individual goals.</small>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Goal Level</label>
                <select class="form-select" name="level" required data-level-select>
                    <option value="department" @selected(old('level') === 'department')>Department</option>
                    <option value="section" @selected(old('level') === 'section')>Section</option>
                    <option value="unit" @selected(old('level') === 'unit')>Unit</option>
                    <option value="individual" @selected(old('level') === 'individual')>Individual</option>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label fw-semibold">Main Goal Title</label>
                <input class="form-control" name="title" value="{{ old('title') }}" placeholder="Improve ICT Service Delivery" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Success Measure / Metric</label>
                <input class="form-control" name="primary_metric" value="{{ old('primary_metric') }}" placeholder="Example: 95% of staff computers operational" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Deadline</label>
                <input class="form-control" type="date" name="deadline" value="{{ old('deadline') }}" required data-goal-deadline>
                <small class="text-muted" data-goal-deadline-help>Select a quarter first.</small>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Main Goal Scope</label>
                <textarea class="form-control" name="specific" rows="2" placeholder="Describe the broad result this goal should achieve. Keep the detailed tasks for the objectives below." required>{{ old('specific') }}</textarea>
                <div class="field-hint">Use this to define the main goal direction, not every activity.</div>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Why This Is Achievable and Matters</label>
                <textarea class="form-control" name="relevant" rows="3" placeholder="Why is this realistic, and why does it matter to the role, team, or mission?" required>{{ old('relevant') }}</textarea>
            </div>
        </div>

        <hr class="my-4">

        <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
            <div>
                <label class="form-label fw-semibold mb-0">Objectives / Sub-Goals</label>
                <div class="field-hint mb-0">Use objectives to specify the actual deliverables that make the main goal happen.</div>
            </div>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-add-objective>Add Objective</button>
        </div>

        @error('objectives')
            <div class="alert alert-danger py-2 small">{{ $message }}</div>
        @enderror

        <div class="d-grid gap-3 mb-3" data-objectives-list>
            @php
                $oldObjectives = old('objectives', [
                    ['title' => '', 'specific_output' => '', 'weight' => '', 'planned_weeks' => '', 'reporting_frequency' => 'weekly', 'starts_at' => '', 'due_at' => ''],
                    ['title' => '', 'specific_output' => '', 'weight' => '', 'planned_weeks' => '', 'reporting_frequency' => 'weekly', 'starts_at' => '', 'due_at' => ''],
                ]);
            @endphp

            @foreach ($oldObjectives as $index => $objective)
                <div class="objective-row" data-objective-row>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong class="small">Objective {{ $index + 1 }}</strong>
                        <button class="btn btn-sm btn-outline-danger" type="button" data-remove-objective>Remove</button>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <input class="form-control" name="objectives[{{ $index }}][title]" value="{{ $objective['title'] ?? '' }}" placeholder="Objective title" required>
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
                            <select class="form-select" name="objectives[{{ $index }}][reporting_frequency]" required>
                                <option value="daily" @selected(($objective['reporting_frequency'] ?? 'weekly') === 'daily')>Daily</option>
                                <option value="weekly" @selected(($objective['reporting_frequency'] ?? 'weekly') === 'weekly')>Weekly</option>
                                <option value="monthly" @selected(($objective['reporting_frequency'] ?? 'weekly') === 'monthly')>Monthly</option>
                            </select>
                            <small class="text-muted">Report cadence</small>
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
                            <label class="form-label small fw-semibold">Objective Deliverable / Evidence</label>
                            <textarea class="form-control" name="objectives[{{ $index }}][specific_output]" rows="3" placeholder="What will this objective deliver, and what evidence will show it is complete?" required>{{ $objective['specific_output'] ?? '' }}</textarea>
                        </div>
                        <div class="col-12">
                            <div class="small fw-semibold text-muted" data-planned-weeks-preview>Choose start and due dates to preview planned reporting weeks.</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="small text-muted mb-4">Example: 20 + 25 + 20 + 15 + 20 = 100%. Official progress comes from supervisor-approved progress updates.</div>

        <div class="d-flex flex-column flex-sm-row gap-2">
            <button class="btn btn-maroon">Save Main Goal</button>
            <a class="btn btn-outline-secondary" href="{{ route('goals.index') }}">Cancel</a>
        </div>
    </form>

    <script>
        const objectivesList = document.querySelector('[data-objectives-list]');
        const addObjectiveButton = document.querySelector('[data-add-objective]');
        const levelSelect = document.querySelector('[data-level-select]');
        const departmentDropdown = document.querySelector('[data-department-dropdown]');
        const departmentToggle = document.querySelector('[data-department-toggle]');
        const departmentMenu = document.querySelector('[data-department-menu]');
        const departmentSummary = document.querySelector('[data-department-summary]');
        const departmentCheckboxes = Array.from(document.querySelectorAll('[data-department-checkbox]'));
        const sectionScope = document.querySelector('[data-section-scope]');
        const unitScope = document.querySelector('[data-unit-scope]');
        const sectionSelect = document.querySelector('[data-section-select]');
        const unitSelect = document.querySelector('[data-unit-select]');

        function selectedDepartmentIds() {
            return departmentCheckboxes.filter((checkbox) => checkbox.checked).map((checkbox) => String(checkbox.value));
        }

        function syncDepartmentSummary() {
            if (! departmentSummary) {
                return;
            }

            const selected = departmentCheckboxes
                .filter((checkbox) => checkbox.checked)
                .map((checkbox) => checkbox.dataset.departmentName);

            if (selected.length === 0) {
                departmentSummary.textContent = 'Select departments';
            } else if (selected.length <= 2) {
                departmentSummary.textContent = selected.join(', ');
            } else {
                departmentSummary.textContent = `${selected.length} departments selected`;
            }
        }

        function closeDepartmentDropdown() {
            departmentMenu?.classList.add('d-none');
            departmentToggle?.setAttribute('aria-expanded', 'false');
        }

        departmentToggle?.addEventListener('click', () => {
            const isOpen = ! departmentMenu?.classList.contains('d-none');

            departmentMenu?.classList.toggle('d-none', isOpen);
            departmentToggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        });

        document.addEventListener('click', (event) => {
            if (! departmentDropdown?.contains(event.target)) {
                closeDepartmentDropdown();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeDepartmentDropdown();
            }
        });

        departmentCheckboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                syncDepartmentSummary();
                syncGoalScopeFields();
            });
        });

        syncDepartmentSummary();

        function syncGoalScopeFields() {
            const level = levelSelect?.value || 'department';
            const departmentIds = selectedDepartmentIds();
            const showSections = level === 'section';
            const showUnits = level === 'unit' || level === 'individual';

            sectionScope?.classList.toggle('d-none', ! showSections);
            unitScope?.classList.toggle('d-none', ! showUnits);

            if (sectionSelect) {
                sectionSelect.disabled = ! showSections;
                Array.from(sectionSelect.options).forEach((option) => {
                    const matchesDepartment = departmentIds.length === 0 || departmentIds.includes(String(option.dataset.departmentId));
                    option.hidden = ! matchesDepartment;
                    option.disabled = ! showSections || ! matchesDepartment;

                    if (option.selected && option.disabled) {
                        option.selected = false;
                    }
                });
            }

            if (unitSelect) {
                unitSelect.disabled = ! showUnits;
                Array.from(unitSelect.options).forEach((option) => {
                    const matchesDepartment = departmentIds.length === 0 || departmentIds.includes(String(option.dataset.departmentId));
                    option.hidden = ! matchesDepartment;
                    option.disabled = ! showUnits || ! matchesDepartment;

                    if (option.selected && option.disabled) {
                        option.selected = false;
                    }
                });
            }
        }

        function renumberObjectives() {
            objectivesList.querySelectorAll('[data-objective-row]').forEach((row, index) => {
                row.querySelector('strong').textContent = `Objective ${index + 1}`;
                row.querySelectorAll('input, textarea, select').forEach((field) => {
                    field.name = field.name.replace(/objectives\[\d+\]/, `objectives[${index}]`);
                });
            });
        }

        addObjectiveButton?.addEventListener('click', () => {
            const index = objectivesList.querySelectorAll('[data-objective-row]').length;
            const wrapper = document.createElement('div');
            wrapper.className = 'objective-row';
            wrapper.dataset.objectiveRow = '';
            wrapper.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong class="small">Objective ${index + 1}</strong>
                    <button class="btn btn-sm btn-outline-danger" type="button" data-remove-objective>Remove</button>
                </div>
                <div class="row g-2">
                    <div class="col-md-4">
                        <input class="form-control" name="objectives[${index}][title]" placeholder="Objective title" required>
                    </div>
                    <div class="col-md-2">
                        <input class="form-control" type="number" min="1" max="100" name="objectives[${index}][weight]" placeholder="Weight %" required>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="objectives[${index}][planned_weeks]" data-objective-weeks required>
                            <option value="">Weeks</option>
                            ${Array.from({ length: 13 }, (_, i) => `<option value="${i + 1}">${i + 1} week${i === 0 ? '' : 's'}</option>`).join('')}
                        </select>
                        <small class="text-muted">Planned duration</small>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="objectives[${index}][reporting_frequency]" required>
                            <option value="daily">Daily</option>
                            <option value="weekly" selected>Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                        <small class="text-muted">Report cadence</small>
                    </div>
                    <div class="col-md-2">
                        <input class="form-control" type="date" name="objectives[${index}][starts_at]" required>
                        <small class="text-muted" data-objective-date-help>Start date</small>
                    </div>
                    <div class="col-md-2">
                        <input class="form-control" type="date" name="objectives[${index}][due_at]" readonly required>
                        <small class="text-muted" data-objective-date-help>Auto end date</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Objective Deliverable / Evidence</label>
                        <textarea class="form-control" name="objectives[${index}][specific_output]" rows="3" placeholder="What will this objective deliver, and what evidence will show it is complete?" required></textarea>
                    </div>
                    <div class="col-12">
                        <div class="small fw-semibold text-muted" data-planned-weeks-preview>Choose start and due dates to preview planned reporting weeks.</div>
                    </div>
                </div>
            `;
            objectivesList.appendChild(wrapper);
            applyObjectiveDateLimits();
            updatePlannedWeekPreviews();
        });

        objectivesList?.addEventListener('click', (event) => {
            if (! event.target.matches('[data-remove-objective]')) {
                return;
            }

            if (objectivesList.querySelectorAll('[data-objective-row]').length === 1) {
                return;
            }

            event.target.closest('[data-objective-row]').remove();
            renumberObjectives();
            updatePlannedWeekPreviews();
        });

        function applyObjectiveDateLimits() {
            const selectedQuarter = document.querySelector('[name="quarter_id"]')?.selectedOptions[0];
            const start = selectedQuarter?.dataset.start || '';
            const end = selectedQuarter?.dataset.end || '';
            const goalDeadline = document.querySelector('[data-goal-deadline]');
            const goalDeadlineHelp = document.querySelector('[data-goal-deadline-help]');

            if (goalDeadline) {
                goalDeadline.min = start;
                goalDeadline.max = end;
            }

            if (goalDeadlineHelp) {
                goalDeadlineHelp.textContent = start && end
                    ? `Deadline must be between ${start} and ${end}.`
                    : 'Select a quarter first.';
            }

            objectivesList.querySelectorAll('[data-objective-row]').forEach((row) => {
                const dateInputs = row.querySelectorAll('input[type="date"]');
                const helps = row.querySelectorAll('[data-objective-date-help]');

                dateInputs.forEach((dateInput) => {
                    dateInput.min = start;
                    dateInput.max = end;
                });

                helps.forEach((help, index) => {
                    const label = index === 0 ? 'Start date' : 'Due date';
                    help.textContent = start && end
                        ? `${label}: ${start} to ${end}.`
                        : `${label}: select a quarter first.`;
                });
            });
        }

        function objectiveWeeks(start, end) {
            if (! start || ! end) {
                return null;
            }

            const startDate = new Date(`${start}T00:00:00`);
            const endDate = new Date(`${end}T00:00:00`);

            if (Number.isNaN(startDate.getTime()) || Number.isNaN(endDate.getTime()) || endDate < startDate) {
                return null;
            }

            return Math.min(13, Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24 * 7)) + 1);
        }

        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        }

        function maxWeeksForStart(start, quarterEnd) {
            if (! start || ! quarterEnd) {
                return 13;
            }

            const startDate = new Date(`${start}T00:00:00`);
            const endDate = new Date(`${quarterEnd}T00:00:00`);

            if (Number.isNaN(startDate.getTime()) || Number.isNaN(endDate.getTime()) || endDate < startDate) {
                return 0;
            }

            return Math.min(13, Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24 * 7)) + 1);
        }

        function updateWeekOptions(row) {
            const selectedQuarter = document.querySelector('[name="quarter_id"]')?.selectedOptions[0];
            const quarterEnd = selectedQuarter?.dataset.end || '';
            const startsAt = row.querySelector('[name$="[starts_at]"]')?.value;
            const weekSelect = row.querySelector('[data-objective-weeks]');
            const maxWeeks = maxWeeksForStart(startsAt, quarterEnd);

            if (! weekSelect) {
                return;
            }

            weekSelect.querySelectorAll('option[value]').forEach((option) => {
                option.disabled = Number(option.value) > maxWeeks;
            });

            if (weekSelect.value && Number(weekSelect.value) > maxWeeks) {
                weekSelect.value = '';
            }
        }

        function updateObjectiveDueDate(row) {
            updateWeekOptions(row);

            const startsAt = row.querySelector('[name$="[starts_at]"]')?.value;
            const weekSelect = row.querySelector('[data-objective-weeks]');
            const dueInput = row.querySelector('[name$="[due_at]"]');
            const weeks = Number(weekSelect?.value || 0);

            if (! dueInput) {
                return;
            }

            if (! startsAt || ! weeks) {
                dueInput.value = '';
                return;
            }

            const dueDate = new Date(`${startsAt}T00:00:00`);
            dueDate.setDate(dueDate.getDate() + (weeks * 7) - 1);
            dueInput.value = formatDate(dueDate);
        }

        function hydratePlannedWeekSelections() {
            objectivesList.querySelectorAll('[data-objective-row]').forEach((row) => {
                const startsAt = row.querySelector('[name$="[starts_at]"]')?.value;
                const dueAt = row.querySelector('[name$="[due_at]"]')?.value;
                const weekSelect = row.querySelector('[data-objective-weeks]');

                if (weekSelect && ! weekSelect.value && startsAt && dueAt) {
                    weekSelect.value = objectiveWeeks(startsAt, dueAt) || '';
                }

                updateWeekOptions(row);
            });
        }

        function updatePlannedWeekPreviews() {
            objectivesList.querySelectorAll('[data-objective-row]').forEach((row) => {
                const startsAt = row.querySelector('[name$="[starts_at]"]')?.value;
                const dueAt = row.querySelector('[name$="[due_at]"]')?.value;
                const selectedQuarter = document.querySelector('[name="quarter_id"]')?.selectedOptions[0];
                const quarterEnd = selectedQuarter?.dataset.end || '';
                const preview = row.querySelector('[data-planned-weeks-preview]');
                const end = dueAt || quarterEnd;
                const weeks = objectiveWeeks(startsAt, end);

                if (! preview) {
                    return;
                }

                preview.textContent = weeks
                    ? `${weeks} planned reporting week${weeks === 1 ? '' : 's'} for this objective.`
                    : 'Choose valid start and due dates to preview planned reporting weeks.';
            });
        }

        levelSelect?.addEventListener('change', syncGoalScopeFields);
        syncGoalScopeFields();

        document.querySelector('[name="quarter_id"]')?.addEventListener('change', applyObjectiveDateLimits);
        document.querySelector('[name="quarter_id"]')?.addEventListener('change', () => {
            objectivesList.querySelectorAll('[data-objective-row]').forEach(updateObjectiveDueDate);
            updatePlannedWeekPreviews();
        });
        objectivesList?.addEventListener('change', (event) => {
            if (event.target.matches('input[type="date"], [data-objective-weeks]')) {
                updateObjectiveDueDate(event.target.closest('[data-objective-row]'));
                updatePlannedWeekPreviews();
            }
        });
        applyObjectiveDateLimits();
        hydratePlannedWeekSelections();
        updatePlannedWeekPreviews();
    </script>
</x-app-layout>
