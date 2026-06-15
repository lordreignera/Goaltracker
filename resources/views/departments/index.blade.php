<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Departments</h1>
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
        <div class="d-flex flex-column flex-lg-row gap-3 justify-content-between">
            <form method="get" action="{{ route('departments.index') }}" class="flex-grow-1">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-9">
                        <label class="form-label small fw-semibold">Search Departments</label>
                        <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Department name, code, or description">
                    </div>
                    <div class="col-lg-3 d-grid">
                        <button class="btn btn-maroon">Filter</button>
                    </div>
                </div>
            </form>
            <div class="d-grid align-self-lg-end">
                <button class="btn btn-maroon" type="button" data-bs-toggle="modal" data-bs-target="#createDepartmentModal">
                    Add Department
                </button>
            </div>
        </div>
    </div>

    <div class="admin-panel table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Code</th>
                    <th>Units</th>
                    <th>Users</th>
                    <th>Goals</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($departments as $department)
                <tr>
                    <td>
                        <strong>{{ $department->name }}</strong>
                        @if ($department->description)
                            <br><small class="text-muted">{{ $department->description }}</small>
                        @endif
                    </td>
                    <td><span class="badge text-bg-light border">{{ $department->code ?: '-' }}</span></td>
                    <td>{{ $department->units_count }}</td>
                    <td>{{ $department->users_count }}</td>
                    <td>{{ $department->goals_count }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#editDepartmentModal{{ $department->id }}">
                            Edit
                        </button>
                        <form method="post" action="{{ route('departments.destroy', $department) }}" class="d-inline" onsubmit="return confirm('Delete this department?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-4 text-muted">No departments match your filters.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $departments->links() }}</div>

    <div class="modal fade" id="createDepartmentModal" tabindex="-1" aria-labelledby="createDepartmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="post" action="{{ route('departments.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title h5 fw-bold" id="createDepartmentModalLabel">Add Department</h2>
                        <div class="text-muted small">Create a main organization department.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold">Department Name</label>
                    <input class="form-control mb-3" name="name" value="{{ old('name') }}" placeholder="ICT Department" required>

                    <label class="form-label fw-semibold">Generated Code</label>
                    <input class="form-control mb-3" value="{{ $nextDepartmentCode }}" readonly>
                    <div class="text-muted small mb-3">The final unique 6-digit code is generated when you save.</div>

                    <label class="form-label fw-semibold">Description</label>
                    <textarea class="form-control" name="description" rows="4" placeholder="Department purpose or notes">{{ old('description') }}</textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-maroon">Save Department</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($departments as $department)
        <div class="modal fade" id="editDepartmentModal{{ $department->id }}" tabindex="-1" aria-labelledby="editDepartmentModalLabel{{ $department->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="post" action="{{ route('departments.update', $department) }}" class="modal-content">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title h5 fw-bold" id="editDepartmentModalLabel{{ $department->id }}">Edit Department</h2>
                            <div class="text-muted small">Update department details.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label fw-semibold">Department Name</label>
                        <input class="form-control mb-3" name="name" value="{{ old('name', $department->name) }}" required>

                        <label class="form-label fw-semibold">Code</label>
                        <input class="form-control mb-3" value="{{ $department->code ?: 'Not generated' }}" readonly>

                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" name="description" rows="4">{{ old('description', $department->description) }}</textarea>
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
