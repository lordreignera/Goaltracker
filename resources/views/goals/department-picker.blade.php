@php
    $selectedDepartmentIds = collect(old('department_ids', $selectedDepartmentIds ?? []))
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values();
    $selectedDepartmentNames = $departments
        ->whereIn('id', $selectedDepartmentIds)
        ->pluck('name');
@endphp

@once
    <style>
        .department-picker { position: relative; }
        .department-picker-toggle { min-height: 42px; text-align: left; }
        .department-picker-menu {
            position: absolute;
            inset: calc(100% + 5px) 0 auto;
            z-index: 1080;
            max-height: 270px;
            overflow-y: auto;
            border: 1px solid var(--sg-border);
            border-radius: 8px;
            background: var(--sg-surface);
            box-shadow: var(--sg-shadow);
            padding: 8px;
        }
        .department-picker-option {
            display: flex;
            align-items: center;
            gap: 9px;
            margin: 0;
            padding: 8px;
            border-radius: 6px;
            cursor: pointer;
        }
        .department-picker-option:hover { background: var(--sg-surface-soft); }
    </style>
@endonce

<label class="form-label fw-semibold">Office / Department</label>
<div class="department-picker" data-department-picker>
    <button class="form-select department-picker-toggle" type="button" aria-expanded="false" data-department-picker-toggle>
        <span data-department-picker-summary>
            {{ $selectedDepartmentNames->isEmpty() ? 'Select departments' : $selectedDepartmentNames->join(', ') }}
        </span>
    </button>

    <div class="department-picker-menu d-none" data-department-picker-menu>
        <label class="department-picker-option border-bottom mb-1 pb-2">
            <input class="form-check-input mt-0" type="checkbox" data-department-select-all>
            <span class="fw-semibold">Select all departments</span>
        </label>

        @foreach ($departments as $department)
            <label class="department-picker-option">
                <input
                    class="form-check-input mt-0"
                    type="checkbox"
                    name="department_ids[]"
                    value="{{ $department->id }}"
                    data-department-checkbox
                    data-department-name="{{ $department->name }}"
                    @checked($selectedDepartmentIds->contains((int) $department->id))
                >
                <span>{{ $department->name }}</span>
            </label>
        @endforeach
    </div>
</div>
<small class="text-muted">Select one, several, or all departments available to you.</small>

@once
    <script>
        document.querySelectorAll('[data-department-picker]').forEach((picker) => {
            const toggle = picker.querySelector('[data-department-picker-toggle]');
            const menu = picker.querySelector('[data-department-picker-menu]');
            const summary = picker.querySelector('[data-department-picker-summary]');
            const selectAll = picker.querySelector('[data-department-select-all]');
            const checkboxes = Array.from(picker.querySelectorAll('[data-department-checkbox]'));

            function syncDepartmentPicker() {
                const selected = checkboxes.filter((checkbox) => checkbox.checked);
                const names = selected.map((checkbox) => checkbox.dataset.departmentName);

                summary.textContent = names.length === 0
                    ? 'Select departments'
                    : names.length <= 2
                        ? names.join(', ')
                        : `${names.length} departments selected`;

                selectAll.checked = checkboxes.length > 0 && selected.length === checkboxes.length;
                selectAll.indeterminate = selected.length > 0 && selected.length < checkboxes.length;
            }

            toggle.addEventListener('click', () => {
                const opening = menu.classList.contains('d-none');
                menu.classList.toggle('d-none', ! opening);
                toggle.setAttribute('aria-expanded', opening ? 'true' : 'false');
            });

            selectAll.addEventListener('change', () => {
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = selectAll.checked;
                });
                syncDepartmentPicker();
            });

            checkboxes.forEach((checkbox) => checkbox.addEventListener('change', syncDepartmentPicker));

            document.addEventListener('click', (event) => {
                if (! picker.contains(event.target)) {
                    menu.classList.add('d-none');
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });

            syncDepartmentPicker();
        });
    </script>
@endonce
