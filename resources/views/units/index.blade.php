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
            <form method="post" action="{{ route('units.store') }}" class="admin-panel p-4">
                @csrf
                <h2 class="h5 fw-bold mb-1">Add Unit</h2>
                <p class="text-muted small mb-3">Create a unit under a department.</p>

                <label class="form-label fw-semibold">Department</label>
                <select class="form-select mb-3" name="department_id" required>
                    <option value="">Select department</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>

                <label class="form-label fw-semibold">Unit Name</label>
                <input class="form-control mb-3" name="name" value="{{ old('name') }}" placeholder="Software Development Unit" required>

                <label class="form-label fw-semibold">Code</label>
                <input class="form-control mb-3" name="code" value="{{ old('code') }}" placeholder="SDU">

                <label class="form-label fw-semibold">Description</label>
                <textarea class="form-control mb-4" name="description" rows="4" placeholder="Unit purpose or notes">{{ old('description') }}</textarea>

                <button class="btn btn-maroon w-100">Save Unit</button>
            </form>
        </div>

        <div class="col-xl-8">
            <form method="get" action="{{ route('units.index') }}" class="admin-panel p-3 mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold">Search</label>
                        <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Unit name, code, or description">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Department</label>
                        <select class="form-select" name="department_id">
                            <option value="">All departments</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                            @endforeach
                        </select>
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
                            <th>Unit</th>
                            <th>Department</th>
                            <th>Code</th>
                            <th>Users</th>
                            <th>Goals</th>
                            <th></th>
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
                            <td>{{ $unit->code ?: '-' }}</td>
                            <td>{{ $unit->users_count }}</td>
                            <td>{{ $unit->goals_count }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-unit-{{ $unit->id }}">Edit</button>
                                <form method="post" action="{{ route('units.destroy', $unit) }}" class="d-inline" onsubmit="return confirm('Delete this unit?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <tr class="collapse" id="edit-unit-{{ $unit->id }}">
                            <td colspan="6" class="bg-light">
                                <form method="post" action="{{ route('units.update', $unit) }}" class="p-3">
                                    @csrf
                                    @method('PUT')
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label small fw-semibold">Department</label>
                                            <select class="form-select" name="department_id" required>
                                                @foreach ($departments as $department)
                                                    <option value="{{ $department->id }}" @selected($unit->department_id === $department->id)>{{ $department->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-semibold">Unit Name</label>
                                            <input class="form-control" name="name" value="{{ $unit->name }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small fw-semibold">Code</label>
                                            <input class="form-control" name="code" value="{{ $unit->code }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-semibold">Description</label>
                                            <input class="form-control" name="description" value="{{ $unit->description }}">
                                        </div>
                                        <div class="col-md-1 d-grid align-items-end">
                                            <button class="btn btn-maroon">Save</button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-4 text-muted">No units match your filters.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $units->links() }}</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</x-app-layout>
