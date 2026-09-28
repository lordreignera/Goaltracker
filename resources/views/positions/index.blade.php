<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Positions</h1>
    </x-slot>

    <style>
        .admin-panel { border: 1px solid #e6e9ef; border-radius: 8px; background: #fff; box-shadow: 0 10px 28px rgba(20, 24, 31, .04); }
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
            <form method="get" action="{{ route('positions.index') }}" class="flex-grow-1">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-5">
                        <label class="form-label small fw-semibold">Search Positions</label>
                        <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Position title, code, or description">
                    </div>
                    <div class="col-lg-3">
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
                    <div class="col-lg-1 d-grid">
                        <button class="btn btn-maroon">Filter</button>
                    </div>
                </div>
            </form>
            <button class="btn btn-maroon align-self-xl-end" type="button" data-bs-toggle="modal" data-bs-target="#createPositionModal">Add Position</button>
        </div>
    </div>

    <div class="admin-panel table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Department</th>
                    <th>Section</th>
                    <th>Code</th>
                    <th>Users</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($positions as $position)
                    <tr>
                        <td>
                            <strong>{{ $position->title }}</strong>
                            @if ($position->description)
                                <br><small class="text-muted">{{ $position->description }}</small>
                            @endif
                        </td>
                        <td>{{ $position->department->name }}</td>
                        <td>{{ $position->section?->name ?? 'Department position' }}</td>
                        <td><span class="badge text-bg-light border">{{ $position->code ?: '-' }}</span></td>
                        <td>{{ $position->users_count }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#editPositionModal{{ $position->id }}">Edit</button>
                            <form method="post" action="{{ route('positions.destroy', $position) }}" class="d-inline" onsubmit="return confirm('Delete this position?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-4 text-muted">No positions match your filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $positions->links() }}</div>

    <div class="modal fade" id="createPositionModal" tabindex="-1" aria-labelledby="createPositionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="post" action="{{ route('positions.store') }}" class="modal-content">
                @csrf
                <input type="hidden" name="form_context" value="createPositionModal">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title h5 fw-bold" id="createPositionModalLabel">Add Position</h2>
                        <div class="text-muted small">Create a position under a department and section.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('positions.partials.fields', ['position' => null])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-maroon">Save Position</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($positions as $position)
        <div class="modal fade" id="editPositionModal{{ $position->id }}" tabindex="-1" aria-labelledby="editPositionModalLabel{{ $position->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="post" action="{{ route('positions.update', $position) }}" class="modal-content">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_context" value="editPositionModal{{ $position->id }}">
                    <div class="modal-header">
                        <h2 class="modal-title h5 fw-bold" id="editPositionModalLabel{{ $position->id }}">Edit Position</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('positions.partials.fields', ['position' => $position])
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
