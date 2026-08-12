<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Edit Goal</h1>
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

        .key-activity-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 8px;
            align-items: start;
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

    @php
        $selectedDepartments = old('department_ids', $goal->assignedDepartments->pluck('id')->all());
        $selectedSections = old('section_ids', $goal->assignedSections->pluck('id')->all());
        $selectedUnits = old('unit_ids', $goal->assignedUnits->pluck('id')->all());
        $oldObjectives = old('objectives', $goal->objectives->map(function ($objective) {
            return [
                'id' => $objective->id,
                'title' => $objective->title,
                'key_activities' => $objective->keyActivitiesList() ?: [''],
                'specific_output' => $objective->specific_output,
                'weight' => $objective->weight,
                'planned_weeks' => $objective->planned_weeks,
                'reporting_frequency' => $objective->reportingFrequencies(),
                'starts_at' => $objective->starts_at?->toDateString() ?? $goal->quarter->starts_at?->toDateString(),
                'due_at' => $objective->due_at?->toDateString(),
            ];
        })->values()->all());
    @endphp

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
        <div>
            <h2 class="h5 fw-bold mb-1">Edit Goal Set</h2>
            <div class="text-muted small">Keep the goal set context clear, then manage the strategic goals/objectives, key activities, and deliverables.</div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('goals.show', $goal) }}">Back to Goal</a>
    </div>

    <x-validation-errors class="alert alert-danger mb-3" />

    <form method="post" action="{{ route('goals.update', $goal) }}" class="goal-panel p-4">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Quarter</label>
                <select class="form-select" name="quarter_id" required>
                    @foreach ($quarters as $quarter)
                        <option value="{{ $quarter->id }}" data-start="{{ $quarter->starts_at->toDateString() }}" data-end="{{ $quarter->ends_at->toDateString() }}" @selected(old('quarter_id', $goal->quarter_id) == $quarter->id)>
                            {{ $quarter->name }} ({{ $quarter->starts_at->format('M d, Y') }} - {{ $quarter->ends_at->format('M d, Y') }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Goal Pillar</label>
                <select class="form-select" name="goal_pillar_id" required>
                    <option value="">Select goal pillar</option>
                    @foreach ($goalPillars as $goalPillar)
                        <option value="{{ $goalPillar->id }}" @selected(old('goal_pillar_id', $goal->goal_pillar_id) == $goalPillar->id)>
                            {{ $goalPillar->name }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Align this goal set to the annual pillar.</small>
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
                                    @checked(in_array($department->id, $selectedDepartments))>
                                <span>{{ $department->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <small class="text-muted">Choose one or more departments.</small>
            </div>
            <div class="col-md-4" data-section-scope>
                <label class="form-label fw-semibold">Sections</label>
                <div class="checkbox-dropdown" data-section-dropdown>
                    <button class="form-select text-start checkbox-dropdown-toggle" type="button" data-section-toggle aria-expanded="false">
                        <span data-section-summary>Select sections</span>
                    </button>

                    <div class="checkbox-dropdown-menu d-none" data-section-menu>
                        @foreach ($sections as $section)
                            <label class="checkbox-dropdown-option" data-section-option>
                                <input
                                    class="form-check-input mt-0"
                                    type="checkbox"
                                    name="section_ids[]"
                                    value="{{ $section->id }}"
                                    data-section-checkbox
                                    data-section-name="{{ $section->department->name ?? 'Department' }} - {{ $section->name }}"
                                    data-department-id="{{ $section->department_id }}"
                                    @checked(in_array($section->id, $selectedSections))>
                                <span>{{ $section->department->name ?? 'Department' }} - {{ $section->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <small class="text-muted d-block mb-2">Select sections for section-wide goals.</small>
            </div>

            <div class="col-md-4" data-unit-scope>
                <label class="form-label fw-semibold">Lower Units</label>
                <div class="checkbox-dropdown" data-unit-dropdown>
                    <button class="form-select text-start checkbox-dropdown-toggle" type="button" data-unit-toggle aria-expanded="false">
                        <span data-unit-summary>Select units</span>
                    </button>

                    <div class="checkbox-dropdown-menu d-none" data-unit-menu>
                        @foreach ($units as $unit)
                            <label class="checkbox-dropdown-option" data-unit-option>
                                <input
                                    class="form-check-input mt-0"
                                    type="checkbox"
                                    name="unit_ids[]"
                                    value="{{ $unit->id }}"
                                    data-unit-checkbox
                                    data-unit-name="{{ $unit->department->name ?? 'Department' }} - {{ $unit->section->name ?? 'Section' }} - {{ $unit->name }}"
                                    data-department-id="{{ $unit->department_id }}"
                                    data-section-id="{{ $unit->section_id }}"
                                    @checked(in_array($unit->id, $selectedUnits))>
                                <span>{{ $unit->department->name ?? 'Department' }} - {{ $unit->section->name ?? 'Section' }} - {{ $unit->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <small class="text-muted">Select units for unit or individual goals.</small>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Goal Level</label>
                <select class="form-select" name="level" required data-level-select>
                    <option value="department" @selected(old('level', $goal->level) === 'department')>Department</option>
                    <option value="section" @selected(old('level', $goal->level) === 'section')>Section</option>
                    <option value="unit" @selected(old('level', $goal->level) === 'unit')>Unit</option>
                    <option value="individual" @selected(old('level', $goal->level) === 'individual')>Individual</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Goal Set Name</label>
                <textarea class="form-control" name="title" rows="2" required>{{ old('title', $goal->title) }}</textarea>
                <div class="field-hint">Use this to name the quarterly goal set. Manage the actual strategic goals/objectives below.</div>
            </div>
        </div>

        <hr class="my-4">

        <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
            <div>
                <label class="form-label fw-semibold mb-0">Strategic Goals / Objectives, Key Activities & Deliverables</label>
                <div class="field-hint mb-0">Set each scored strategic goal/objective, list its activities, and define the key result areas/deliverables.</div>
            </div>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-add-objective>Add Strategic Goal / Objective</button>
        </div>

        @error('objectives')
            <div class="alert alert-danger py-2 small">{{ $message }}</div>
        @enderror

        <div class="d-grid gap-3 mb-3" data-objectives-list>
            @foreach ($oldObjectives as $index => $objective)
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
                    <input type="hidden" name="objectives[{{ $index }}][id]" value="{{ $objective['id'] ?? '' }}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong class="small">Strategic Goal / Objective {{ $index + 1 }}</strong>
                        <button class="btn btn-sm btn-outline-danger" type="button" data-remove-objective>Remove</button>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <input class="form-control" name="objectives[{{ $index }}][title]" value="{{ $objective['title'] ?? '' }}" placeholder="Strategic goal / objective" required>
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
                            <label class="form-label small fw-semibold">Key Result Areas / Deliverables</label>
                            <textarea class="form-control" name="objectives[{{ $index }}][specific_output]" rows="3" placeholder="What result, deliverable, or evidence should be produced?" required>{{ $objective['specific_output'] ?? '' }}</textarea>
                        </div>
                        <div class="col-12">
                            <div class="small fw-semibold text-muted" data-planned-weeks-preview>Choose start and due dates to preview planned reporting weeks.</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="small text-muted mb-4">Weights must total exactly 100% before saving. Official progress comes from supervisor-approved progress updates.</div>

        <div class="d-flex flex-column flex-sm-row gap-2">
            <button class="btn btn-maroon">Save Changes</button>
            <a class="btn btn-outline-secondary" href="{{ route('goals.show', $goal) }}">Cancel</a>
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
        const sectionDropdown = document.querySelector('[data-section-dropdown]');
        const sectionToggle = document.querySelector('[data-section-toggle]');
        const sectionMenu = document.querySelector('[data-section-menu]');
        const sectionSummary = document.querySelector('[data-section-summary]');
        const sectionCheckboxes = Array.from(document.querySelectorAll('[data-section-checkbox]'));
        const unitDropdown = document.querySelector('[data-unit-dropdown]');
        const unitToggle = document.querySelector('[data-unit-toggle]');
        const unitMenu = document.querySelector('[data-unit-menu]');
        const unitSummary = document.querySelector('[data-unit-summary]');
        const unitCheckboxes = Array.from(document.querySelectorAll('[data-unit-checkbox]'));

        function selectedDepartmentIds() {
            return departmentCheckboxes.filter((checkbox) => checkbox.checked).map((checkbox) => String(checkbox.value));
        }

        function syncDepartmentSummary() {
            syncCheckboxSummary(departmentSummary, departmentCheckboxes, 'Select departments', 'departmentName', 'departments selected');
        }

        function syncSectionSummary() {
            syncCheckboxSummary(sectionSummary, sectionCheckboxes, 'Select sections', 'sectionName', 'sections selected');
        }

        function syncUnitSummary() {
            syncCheckboxSummary(unitSummary, unitCheckboxes, 'Select units', 'unitName', 'units selected');
        }

        function syncCheckboxSummary(summary, checkboxes, emptyText, nameKey, manyText) {
            if (! summary) {
                return;
            }

            const selected = checkboxes
                .filter((checkbox) => checkbox.checked && ! checkbox.disabled)
                .map((checkbox) => checkbox.dataset[nameKey]);

            if (selected.length === 0) {
                summary.textContent = emptyText;
            } else if (selected.length <= 2) {
                summary.textContent = selected.join(', ');
            } else {
                summary.textContent = `${selected.length} ${manyText}`;
            }
        }

        function closeDepartmentDropdown() {
            departmentMenu?.classList.add('d-none');
            departmentToggle?.setAttribute('aria-expanded', 'false');
        }

        function closeSectionDropdown() {
            sectionMenu?.classList.add('d-none');
            sectionToggle?.setAttribute('aria-expanded', 'false');
        }

        function closeUnitDropdown() {
            unitMenu?.classList.add('d-none');
            unitToggle?.setAttribute('aria-expanded', 'false');
        }

        function bindCheckboxDropdown(toggle, menu) {
            toggle?.addEventListener('click', () => {
                const isOpen = ! menu?.classList.contains('d-none');

                menu?.classList.toggle('d-none', isOpen);
                toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            });
        }

        bindCheckboxDropdown(departmentToggle, departmentMenu);
        bindCheckboxDropdown(sectionToggle, sectionMenu);
        bindCheckboxDropdown(unitToggle, unitMenu);

        document.addEventListener('click', (event) => {
            if (! departmentDropdown?.contains(event.target)) {
                closeDepartmentDropdown();
            }

            if (! sectionDropdown?.contains(event.target)) {
                closeSectionDropdown();
            }

            if (! unitDropdown?.contains(event.target)) {
                closeUnitDropdown();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeDepartmentDropdown();
                closeSectionDropdown();
                closeUnitDropdown();
            }
        });

        departmentCheckboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                syncDepartmentSummary();
                syncGoalScopeFields();
            });
        });

        sectionCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', syncSectionSummary));
        unitCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', syncUnitSummary));

        syncDepartmentSummary();
        syncSectionSummary();
        syncUnitSummary();

        function syncGoalScopeFields() {
            const level = levelSelect?.value || 'department';
            const departmentIds = selectedDepartmentIds();
            const showSections = level === 'section';
            const showUnits = level === 'unit' || level === 'individual';

            sectionScope?.classList.toggle('d-none', ! showSections);
            unitScope?.classList.toggle('d-none', ! showUnits);

            sectionToggle?.toggleAttribute('disabled', ! showSections);
            unitToggle?.toggleAttribute('disabled', ! showUnits);

            sectionCheckboxes.forEach((checkbox) => {
                const option = checkbox.closest('[data-section-option]');
                const matchesDepartment = departmentIds.length === 0 || departmentIds.includes(String(checkbox.dataset.departmentId));
                const disabled = ! showSections || ! matchesDepartment;

                option?.classList.toggle('d-none', ! matchesDepartment);
                checkbox.disabled = disabled;

                if (checkbox.checked && disabled) {
                    checkbox.checked = false;
                }
            });

            unitCheckboxes.forEach((checkbox) => {
                const option = checkbox.closest('[data-unit-option]');
                const matchesDepartment = departmentIds.length === 0 || departmentIds.includes(String(checkbox.dataset.departmentId));
                const disabled = ! showUnits || ! matchesDepartment;

                option?.classList.toggle('d-none', ! matchesDepartment);
                checkbox.disabled = disabled;

                if (checkbox.checked && disabled) {
                    checkbox.checked = false;
                }
            });

            syncSectionSummary();
            syncUnitSummary();
        }

        function renumberObjectives() {
            objectivesList.querySelectorAll('[data-objective-row]').forEach((row, index) => {
                row.querySelector('strong').textContent = `Strategic Goal / Objective ${index + 1}`;
                row.querySelectorAll('input, textarea, select').forEach((field) => {
                    field.name = field.name.replace(/objectives\[\d+\]/, `objectives[${index}]`);
                });
                renumberKeyActivities(row);
            });
        }

        function renumberKeyActivities(row) {
            const objectiveIndex = Array.from(objectivesList.querySelectorAll('[data-objective-row]')).indexOf(row);

            row.querySelectorAll('[data-key-activity-row]').forEach((activityRow, activityIndex) => {
                const input = activityRow.querySelector('input');

                if (input) {
                    input.name = `objectives[${objectiveIndex}][key_activities][${activityIndex}]`;
                    input.placeholder = `Key activity ${activityIndex + 1}`;
                }
            });
        }

        addObjectiveButton?.addEventListener('click', () => {
            const index = objectivesList.querySelectorAll('[data-objective-row]').length;
            const wrapper = document.createElement('div');
            wrapper.className = 'objective-row';
            wrapper.dataset.objectiveRow = '';
            wrapper.innerHTML = `
                <input type="hidden" name="objectives[${index}][id]" value="">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong class="small">Strategic Goal / Objective ${index + 1}</strong>
                    <button class="btn btn-sm btn-outline-danger" type="button" data-remove-objective>Remove</button>
                </div>
                <div class="row g-2">
                    <div class="col-md-4">
                        <input class="form-control" name="objectives[${index}][title]" placeholder="Strategic goal / objective" required>
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
                        <label class="form-label small fw-semibold mb-1">Report Cadence</label>
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="objectives[${index}][reporting_frequency][]" value="daily">
                            <span class="form-check-label">Daily</span>
                        </label>
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="objectives[${index}][reporting_frequency][]" value="weekly" checked>
                            <span class="form-check-label">Weekly</span>
                        </label>
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="objectives[${index}][reporting_frequency][]" value="monthly">
                            <span class="form-check-label">Monthly</span>
                        </label>
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
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                            <label class="form-label small fw-semibold mb-0">Key Activities</label>
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-add-key-activity>Add Activity</button>
                        </div>
                        <div class="d-grid gap-2" data-key-activities-list>
                            <div class="key-activity-row" data-key-activity-row>
                                <input class="form-control" name="objectives[${index}][key_activities][0]" placeholder="Key activity 1" required>
                                <button class="btn btn-outline-danger" type="button" data-remove-key-activity>Remove</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Key Result Areas / Deliverables</label>
                        <textarea class="form-control" name="objectives[${index}][specific_output]" rows="3" placeholder="What result, deliverable, or evidence should be produced?" required></textarea>
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
            if (event.target.matches('[data-add-key-activity]')) {
                const row = event.target.closest('[data-objective-row]');
                const list = row?.querySelector('[data-key-activities-list]');

                if (! row || ! list) {
                    return;
                }

                const objectiveIndex = Array.from(objectivesList.querySelectorAll('[data-objective-row]')).indexOf(row);
                const activityIndex = list.querySelectorAll('[data-key-activity-row]').length;
                const wrapper = document.createElement('div');
                wrapper.className = 'key-activity-row';
                wrapper.dataset.keyActivityRow = '';
                wrapper.innerHTML = `
                    <input class="form-control" name="objectives[${objectiveIndex}][key_activities][${activityIndex}]" placeholder="Key activity ${activityIndex + 1}" required>
                    <button class="btn btn-outline-danger" type="button" data-remove-key-activity>Remove</button>
                `;
                list.appendChild(wrapper);

                return;
            }

            if (event.target.matches('[data-remove-key-activity]')) {
                const row = event.target.closest('[data-objective-row]');
                const list = row?.querySelector('[data-key-activities-list]');

                if (! row || ! list || list.querySelectorAll('[data-key-activity-row]').length === 1) {
                    return;
                }

                event.target.closest('[data-key-activity-row]').remove();
                renumberKeyActivities(row);

                return;
            }

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
        levelSelect?.addEventListener('change', syncGoalScopeFields);
        syncGoalScopeFields();

    </script>
</x-app-layout>
