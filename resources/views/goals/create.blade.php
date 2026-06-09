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

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
        <div>
            <h2 class="h5 fw-bold mb-1">Create Main Goal</h2>
            <div class="text-muted small">Add objectives/sub-goals now. Objective weights must total 100%.</div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('goals.index') }}">Back to Goals</a>
    </div>

    <form method="post" action="{{ route('goals.store') }}" class="goal-panel p-4">
        @csrf

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Quarter</label>
                <select class="form-select" name="quarter_id" required>
                    <option value="">Select quarter</option>
                    @foreach ($quarters as $quarter)
                        <option value="{{ $quarter->id }}" @selected(old('quarter_id') == $quarter->id)>{{ $quarter->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Department</label>
                <select class="form-select" name="department_id" required>
                    <option value="">Select department</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Unit</label>
                <select class="form-select" name="unit_id">
                    <option value="">Department-wide goal</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Goal Level</label>
                <select class="form-select" name="level" required>
                    <option value="department" @selected(old('level') === 'department')>Department</option>
                    <option value="unit" @selected(old('level') === 'unit')>Unit</option>
                    <option value="individual" @selected(old('level') === 'individual')>Individual</option>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label fw-semibold">Main Goal Title</label>
                <input class="form-control" name="title" value="{{ old('title') }}" placeholder="Improve ICT Service Delivery" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Expected Outcome</label>
                <textarea class="form-control" name="description" rows="3" placeholder="Describe the outcome expected within the 90-day period">{{ old('description') }}</textarea>
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
            @php
                $oldObjectives = old('objectives', [
                    ['title' => '', 'description' => '', 'weight' => '', 'due_at' => ''],
                    ['title' => '', 'description' => '', 'weight' => '', 'due_at' => ''],
                ]);
            @endphp

            @foreach ($oldObjectives as $index => $objective)
                <div class="objective-row" data-objective-row>
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
                        <div class="col-md-4">
                            <input class="form-control" type="date" name="objectives[{{ $index }}][due_at]" value="{{ $objective['due_at'] ?? '' }}">
                        </div>
                        <div class="col-12">
                            <textarea class="form-control" name="objectives[{{ $index }}][description]" rows="2" placeholder="Specific, measurable objective">{{ $objective['description'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="small text-muted mb-4">Example: 20 + 25 + 20 + 15 + 20 = 100%. Only approved completed objectives contribute to progress.</div>

        <div class="d-flex flex-column flex-sm-row gap-2">
            <button class="btn btn-maroon">Save Main Goal</button>
            <a class="btn btn-outline-secondary" href="{{ route('goals.index') }}">Cancel</a>
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
                    <div class="col-md-4">
                        <input class="form-control" type="date" name="objectives[${index}][due_at]">
                    </div>
                    <div class="col-12">
                        <textarea class="form-control" name="objectives[${index}][description]" rows="2" placeholder="Specific, measurable objective"></textarea>
                    </div>
                </div>
            `;
            objectivesList.appendChild(wrapper);
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
        });
    </script>
</x-app-layout>
