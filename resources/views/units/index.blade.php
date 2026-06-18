<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Units</h1>
    </x-slot>

    <style>
        .admin-panel {
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
    </style>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Please check the form.</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="admin-panel p-3 mb-3">
        <div class="d-flex flex-column flex-xl-row gap-3 justify-content-between">
            <form method="get" action="{{ route('units.index') }}" class="flex-grow-1">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-5">
                        <label class="form-label small fw-semibold">Search Units</label>
                        <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Unit name, code, or description">
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label small fw-semibold">Department</label>
                        <select class="form-select" name="department_id">
                            <option value="">All departments</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label small fw-semibold">Section</label>
                        <select class="form-select" name="section_id">
                            <option value="">All sections</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}" @selected(request('section_id') == $section->id)>{{ $section->department->name }} - {{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-12 d-grid">
                        <button class="btn btn-maroon">Filter</button>
                    </div>
                </div>
            </form>
            <div class="d-grid align-self-xl-end">
                <button class="btn btn-maroon" type="button" data-bs-toggle="modal" data-bs-target="#createUnitModal">
                    Add Unit
                </button>
            </div>
        </div>
    </div>

    <div class="admin-panel table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Unit</th>
                    <th>Department</th>
                    <th>Section</th>
                    <th>Code</th>
                    <th>Users</th>
                    <th>Goals</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($units as $unit)
                <tr>
                    <td>
                        <strong>{{ $unit->name }}</strong>
                        @if ($unit->description)
                            <br><small class="text-muted">{{ $unit->description }}</small>
                        @endif
                    </td>
                    <td>{{ $unit->department->name }}</td>
                    <td>{{ $unit->section->name }}</td>
                    <td><span class="badge text-bg-light border">{{ $unit->code ?: '-' }}</span></td>
                    <td>{{ $unit->users_count }}</td>
                    <td>{{ $unit->goals_count }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#editUnitModal{{ $unit->id }}">
                            Edit
                        </button>
                        <form method="post" action="{{ route('units.destroy', $unit) }}" class="d-inline" onsubmit="return confirm('Delete this unit?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-4 text-muted">No units match your filters.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $units->links() }}</div>

    <div class="modal fade" id="createUnitModal" tabindex="-1" aria-labelledby="createUnitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="post" action="{{ route('units.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title h5 fw-bold" id="createUnitModalLabel">Add Unit</h2>
                        <div class="text-muted small">Create a unit under a department.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold">Department</label>
                    <select class="form-select mb-3" name="department_id" required>
                        <option value="">Select department</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>

                    <label class="form-label fw-semibold">Section</label>
                    <select class="form-select mb-3" name="section_id" required>
                        <option value="">Select section</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->id }}" @selected(old('section_id') == $section->id)>{{ $section->department->name }} - {{ $section->name }}</option>
                        @endforeach
                    </select>

                    <label class="form-label fw-semibold">Unit Name</label>
                    <input class="form-control mb-3" name="name" value="{{ old('name') }}" placeholder="Receptionist" required>

                    <label class="form-label fw-semibold">Code</label>
                    <input class="form-control mb-3" name="code" value="{{ old('code') }}" placeholder="SDU">

                    <label class="form-label fw-semibold">Description</label>
                    <textarea class="form-control" name="description" rows="4" placeholder="Unit purpose or notes">{{ old('description') }}</textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-maroon">Save Unit</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($units as $unit)
        <div class="modal fade" id="editUnitModal{{ $unit->id }}" tabindex="-1" aria-labelledby="editUnitModalLabel{{ $unit->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="post" action="{{ route('units.update', $unit) }}" class="modal-content">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title h5 fw-bold" id="editUnitModalLabel{{ $unit->id }}">Edit Unit</h2>
                            <div class="text-muted small">Update unit details and department alignment.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label fw-semibold">Department</label>
                        <select class="form-select mb-3" name="department_id" required>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected(old('department_id', $unit->department_id) == $department->id)>{{ $department->name }}</option>
                            @endforeach
                        </select>

                        <label class="form-label fw-semibold">Section</label>
                        <select class="form-select mb-3" name="section_id" required>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}" @selected(old('section_id', $unit->section_id) == $section->id)>{{ $section->department->name }} - {{ $section->name }}</option>
                            @endforeach
                        </select>

                        <label class="form-label fw-semibold">Unit Name</label>
                        <input class="form-control mb-3" name="name" value="{{ old('name', $unit->name) }}" required>

                        <label class="form-label fw-semibold">Code</label>
                        <input class="form-control mb-3" name="code" value="{{ old('code', $unit->code) }}">

                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" name="description" rows="4">{{ old('description', $unit->description) }}</textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-maroon">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</x-app-layout>
