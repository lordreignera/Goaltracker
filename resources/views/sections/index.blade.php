<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Sections</h1>
    </x-slot>

    <style>
        .admin-panel { border: 1px solid #e6e9ef; border-radius: 14px; background: #fff; box-shadow: 0 10px 28px rgba(20, 24, 31, .04); }
        .btn-maroon { background: var(--arm-maroon); border-color: var(--arm-maroon); color: #fff; font-weight: 800; }
        .btn-maroon:hover { background: var(--arm-maroon-dark); border-color: var(--arm-maroon-dark); color: #fff; }
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
            <form method="get" action="{{ route('sections.index') }}" class="flex-grow-1">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-5">
                        <label class="form-label small fw-semibold">Search Sections</label>
                        <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Section name, code, or description">
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
                    <div class="col-lg-3 d-grid">
                        <button class="btn btn-maroon">Filter</button>
                    </div>
                </div>
            </form>
            <div class="d-grid align-self-xl-end">
                <button class="btn btn-maroon" type="button" data-bs-toggle="modal" data-bs-target="#createSectionModal">Add Section</button>
            </div>
        </div>
    </div>

    <div class="admin-panel table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Section</th>
                    <th>Department</th>
                    <th>Code</th>
                    <th>Units</th>
                    <th>Users</th>
                    <th>Goals</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($sections as $section)
                <tr>
                    <td>
                        <strong>{{ $section->name }}</strong>
                        @if ($section->description)
                            <br><small class="text-muted">{{ $section->description }}</small>
                        @endif
                    </td>
                    <td>{{ $section->department->name }}</td>
                    <td><span class="badge text-bg-light border">{{ $section->code ?: '-' }}</span></td>
                    <td>{{ $section->units_count }}</td>
                    <td>{{ $section->users_count }}</td>
                    <td>{{ $section->goals_count }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#editSectionModal{{ $section->id }}">Edit</button>
                        <form method="post" action="{{ route('sections.destroy', $section) }}" class="d-inline" onsubmit="return confirm('Delete this section?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-4 text-muted">No sections match your filters.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $sections->links() }}</div>

    <div class="modal fade" id="createSectionModal" tabindex="-1" aria-labelledby="createSectionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="post" action="{{ route('sections.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title h5 fw-bold" id="createSectionModalLabel">Add Section</h2>
                        <div class="text-muted small">Create a section under a department.</div>
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

                    <label class="form-label fw-semibold">Section Name</label>
                    <input class="form-control mb-3" name="name" value="{{ old('name') }}" placeholder="Human Resource" required>

                    <label class="form-label fw-semibold">Code</label>
                    <input class="form-control mb-3" name="code" value="{{ old('code') }}" placeholder="HR">

                    <label class="form-label fw-semibold">Description</label>
                    <textarea class="form-control" name="description" rows="4" placeholder="Section purpose or notes">{{ old('description') }}</textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-maroon">Save Section</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($sections as $section)
        <div class="modal fade" id="editSectionModal{{ $section->id }}" tabindex="-1" aria-labelledby="editSectionModalLabel{{ $section->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="post" action="{{ route('sections.update', $section) }}" class="modal-content">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title h5 fw-bold" id="editSectionModalLabel{{ $section->id }}">Edit Section</h2>
                            <div class="text-muted small">Update section details and department alignment.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label fw-semibold">Department</label>
                        <select class="form-select mb-3" name="department_id" required>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected(old('department_id', $section->department_id) == $department->id)>{{ $department->name }}</option>
                            @endforeach
                        </select>

                        <label class="form-label fw-semibold">Section Name</label>
                        <input class="form-control mb-3" name="name" value="{{ old('name', $section->name) }}" required>

                        <label class="form-label fw-semibold">Code</label>
                        <input class="form-control mb-3" name="code" value="{{ old('code', $section->code) }}">

                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" name="description" rows="4">{{ old('description', $section->description) }}</textarea>
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
