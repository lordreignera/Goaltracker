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
            background: #8f171b;
            border-color: #8f171b;
            color: #fff;
            font-weight: 800;
        }

        .btn-maroon:hover {
            background: #721216;
            border-color: #721216;
            color: #fff;
        }

        .objective-row {
            border: 1px solid #e6e9ef;
            border-radius: 12px;
            background: #fbfcfd;
            padding: 14px;
        }
    </style>

    @php
        $selectedDepartments = old('department_ids', $goal->assignedDepartments->pluck('id')->all() ?: [$goal->department_id]);
        $selectedUnits = old('unit_ids', $goal->assignedUnits->pluck('id')->all() ?: array_filter([$goal->unit_id]));
        $oldObjectives = old('objectives', $goal->objectives->map(fn ($objective) => [
            'id' => $objective->id,
            'title' => $objective->title,
            'description' => $objective->description,
            'weight' => $objective->weight,
            'starts_at' => $objective->starts_at?->toDateString() ?? $goal->quarter->starts_at?->toDateString(),
            'due_at' => $objective->due_at?->toDateString(),
        ])->values()->all());
    @endphp

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
        <div>
            <h2 class="h5 fw-bold mb-1">Edit Main Goal & Objectives</h2>
            <div class="text-muted small">Add/remove objectives here only. The total weight must remain 100%.</div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('goals.show', $goal) }}">Back to Goal</a>
    </div>

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
                <label class="form-label fw-semibold">Departments</label>
                <select class="form-select" name="department_ids[]" multiple required size="5">
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(in_array($department->id, $selectedDepartments))>{{ $department->name }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Hold Ctrl to select more than one.</small>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Units</label>
                <select class="form-select" name="unit_ids[]" multiple size="5">
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected(in_array($unit->id, $selectedUnits))>{{ $unit->department->name ?? 'Department' }} - {{ $unit->name }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Leave empty for department-wide assignment.</small>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Goal Level</label>
                <select class="form-select" name="level" required>
                    <option value="department" @selected(old('level', $goal->level) === 'department')>Department</option>
                    <option value="unit" @selected(old('level', $goal->level) === 'unit')>Unit</option>
                    <option value="individual" @selected(old('level', $goal->level) === 'individual')>Individual</option>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label fw-semibold">Main Goal Title</label>
                <input class="form-control" name="title" value="{{ old('title', $goal->title) }}" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Expected Outcome</label>
                <textarea class="form-control" name="description" rows="3">{{ old('description', $goal->description) }}</textarea>
            </div>
        </div>

        <hr class="my-4">

        <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
            <label class="form-label fw-semibold mb-0">Objectives / Sub-Goals</label>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-add-objective>Add Objective</button>
        </div>

        @error('objectives')
            <div class="alert alert-danger py-2 small">{{ $message }}</div>
        @enderror

        <div class="d-grid gap-3 mb-3" data-objectives-list>
            @foreach ($oldObjectives as $index => $objective)
                <div class="objective-row" data-objective-row>
                    <input type="hidden" name="objectives[{{ $index }}][id]" value="{{ $objective['id'] ?? '' }}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong class="small">Objective {{ $index + 1 }}</strong>
                        <button class="btn btn-sm btn-outline-danger" type="button" data-remove-objective>Remove</button>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-5">
                            <input class="form-control" name="objectives[{{ $index }}][title]" value="{{ $objective['title'] ?? '' }}" placeholder="Objective title" required>
                        </div>
                        <div class="col-md-3">
                            <input class="form-control" type="number" min="1" max="100" name="objectives[{{ $index }}][weight]" value="{{ $objective['weight'] ?? '' }}" placeholder="Weight %" required>
                        </div>
                        <div class="col-md-2">
                            <input class="form-control" type="date" name="objectives[{{ $index }}][starts_at]" value="{{ $objective['starts_at'] ?? '' }}" required>
                            <small class="text-muted" data-objective-date-help>Start date</small>
                        </div>
                        <div class="col-md-2">
                            <input class="form-control" type="date" name="objectives[{{ $index }}][due_at]" value="{{ $objective['due_at'] ?? '' }}">
                            <small class="text-muted" data-objective-date-help>Due date</small>
                        </div>
                        <div class="col-12">
                            <textarea class="form-control" name="objectives[{{ $index }}][description]" rows="2" placeholder="Specific, measurable objective">{{ $objective['description'] ?? '' }}</textarea>
                        </div>
                        <div class="col-12">
                            <div class="small fw-semibold text-muted" data-planned-weeks-preview>Choose start and due dates to preview planned reporting weeks.</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="small text-muted mb-4">Weights must total exactly 100% before saving.</div>

        <div class="d-flex flex-column flex-sm-row gap-2">
            <button class="btn btn-maroon">Save Changes</button>
            <a class="btn btn-outline-secondary" href="{{ route('goals.show', $goal) }}">Cancel</a>
        </div>
    </form>

    <script>
        const objectivesList = document.querySelector('[data-objectives-list]');
        const addObjectiveButton = document.querySelector('[data-add-objective]');

        function renumberObjectives() {
            objectivesList.querySelectorAll('[data-objective-row]').forEach((row, index) => {
                row.querySelector('strong').textContent = `Objective ${index + 1}`;
                row.querySelectorAll('input, textarea').forEach((field) => {
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
                <input type="hidden" name="objectives[${index}][id]" value="">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong class="small">Objective ${index + 1}</strong>
                    <button class="btn btn-sm btn-outline-danger" type="button" data-remove-objective>Remove</button>
                </div>
                <div class="row g-2">
                    <div class="col-md-5">
                        <input class="form-control" name="objectives[${index}][title]" placeholder="Objective title" required>
                    </div>
                    <div class="col-md-3">
                        <input class="form-control" type="number" min="1" max="100" name="objectives[${index}][weight]" placeholder="Weight %" required>
                    </div>
                    <div class="col-md-2">
                        <input class="form-control" type="date" name="objectives[${index}][starts_at]" required>
                        <small class="text-muted" data-objective-date-help>Start date</small>
                    </div>
                    <div class="col-md-2">
                        <input class="form-control" type="date" name="objectives[${index}][due_at]">
                        <small class="text-muted" data-objective-date-help>Due date</small>
                    </div>
                    <div class="col-12">
                        <textarea class="form-control" name="objectives[${index}][description]" rows="2" placeholder="Specific, measurable objective"></textarea>
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
        document.querySelector('[name="quarter_id"]')?.addEventListener('change', updatePlannedWeekPreviews);
        objectivesList?.addEventListener('change', (event) => {
            if (event.target.matches('input[type="date"]')) {
                updatePlannedWeekPreviews();
            }
        });
        applyObjectiveDateLimits();
        updatePlannedWeekPreviews();
    </script>
</x-app-layout>
