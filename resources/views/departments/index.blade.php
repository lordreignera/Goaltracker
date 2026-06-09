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

    <div class="row g-4">
        <div class="col-xl-4">
            <form method="post" action="{{ route('departments.store') }}" class="admin-panel p-4">
                @csrf
                <h2 class="h5 fw-bold mb-1">Add Department</h2>
                <p class="text-muted small mb-3">Create the main organization department.</p>

                <label class="form-label fw-semibold">Department Name</label>
                <input class="form-control mb-3" name="name" value="{{ old('name') }}" placeholder="ICT Department" required>

                <label class="form-label fw-semibold">Code</label>
                <input class="form-control mb-3" name="code" value="{{ old('code') }}" placeholder="ICT">

                <label class="form-label fw-semibold">Description</label>
                <textarea class="form-control mb-4" name="description" rows="4" placeholder="Department purpose or notes">{{ old('description') }}</textarea>

                <button class="btn btn-maroon w-100">Save Department</button>
            </form>
        </div>

        <div class="col-xl-8">
            <form method="get" action="{{ route('departments.index') }}" class="admin-panel p-3 mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-9">
                        <label class="form-label small fw-semibold">Search</label>
                        <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Department name, code, or description">
                    </div>
                    <div class="col-md-3 d-grid">
                        <button class="btn btn-maroon">Filter</button>
                    </div>
                </div>
            </form>

            <div class="admin-panel table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Department</th>
                            <th>Code</th>
                            <th>Units</th>
                            <th>Users</th>
                            <th>Goals</th>
                            <th></th>
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
                            <td>{{ $department->code ?: '-' }}</td>
                            <td>{{ $department->units_count }}</td>
                            <td>{{ $department->users_count }}</td>
                            <td>{{ $department->goals_count }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-department-{{ $department->id }}">Edit</button>
                                <form method="post" action="{{ route('departments.destroy', $department) }}" class="d-inline" onsubmit="return confirm('Delete this department?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <tr class="collapse" id="edit-department-{{ $department->id }}">
                            <td colspan="6" class="bg-light">
                                <form method="post" action="{{ route('departments.update', $department) }}" class="p-3">
                                    @csrf
                                    @method('PUT')
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Department Name</label>
                                            <input class="form-control" name="name" value="{{ $department->name }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small fw-semibold">Code</label>
                                            <input class="form-control" name="code" value="{{ $department->code }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Description</label>
                                            <input class="form-control" name="description" value="{{ $department->description }}">
                                        </div>
                                        <div class="col-md-2 d-grid align-items-end">
                                            <button class="btn btn-maroon">Save</button>
                                        </div>
                                    </div>
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
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</x-app-layout>
