<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Goal Pillars</h1>
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
            <form method="get" action="{{ route('goal-pillars.index') }}" class="flex-grow-1">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-9">
                        <label class="form-label small fw-semibold">Search Goal Pillars</label>
                        <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Pillar name, annual goal, or description">
                    </div>
                    <div class="col-lg-3 d-grid">
                        <button class="btn btn-maroon">Filter</button>
                    </div>
                </div>
            </form>
            <div class="d-grid align-self-lg-end">
                <button class="btn btn-maroon" type="button" data-bs-toggle="modal" data-bs-target="#createGoalPillarModal">
                    Add Goal Pillar
                </button>
            </div>
        </div>
    </div>

    <div class="admin-panel table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Pillar</th>
                    <th>Annual Goal</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th>Goals</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($goalPillars as $goalPillar)
                <tr>
                    <td>
                        <strong>{{ $goalPillar->name }}</strong>
                        @if ($goalPillar->description)
                            <br><small class="text-muted">{{ $goalPillar->description }}</small>
                        @endif
                    </td>
                    <td>{{ $goalPillar->annual_goal }}</td>
                    <td>{{ $goalPillar->sort_order }}</td>
                    <td>
                        @if ($goalPillar->is_active)
                            <span class="badge text-bg-success">Active</span>
                        @else
                            <span class="badge text-bg-light border">Inactive</span>
                        @endif
                    </td>
                    <td>{{ $goalPillar->goals_count }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#editGoalPillarModal{{ $goalPillar->id }}">
                            Edit
                        </button>
                        <form method="post" action="{{ route('goal-pillars.destroy', $goalPillar) }}" class="d-inline" onsubmit="return confirm('Delete this goal pillar?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-4 text-muted">No goal pillars match your filters.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $goalPillars->links() }}</div>

    <div class="modal fade" id="createGoalPillarModal" tabindex="-1" aria-labelledby="createGoalPillarModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form method="post" action="{{ route('goal-pillars.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title h5 fw-bold" id="createGoalPillarModalLabel">Add Goal Pillar</h2>
                        <div class="text-muted small">Create a strategic pillar that quarterly goals align to.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold">Pillar Name</label>
                    <input class="form-control mb-3" name="name" value="{{ old('name') }}" placeholder="Operational Excellence" required>

                    <label class="form-label fw-semibold">Annual Goal</label>
                    <textarea class="form-control mb-3" name="annual_goal" rows="3" placeholder="What annual result does this pillar represent?" required>{{ old('annual_goal') }}</textarea>

                    <label class="form-label fw-semibold">Description</label>
                    <textarea class="form-control mb-3" name="description" rows="3" placeholder="Optional notes for staff choosing this pillar.">{{ old('description') }}</textarea>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Sort Order</label>
                            <input class="form-control" type="number" min="0" max="65535" name="sort_order" value="{{ old('sort_order', 0) }}" required>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                                <span class="form-check-label">Active</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-maroon">Save Goal Pillar</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($goalPillars as $goalPillar)
        <div class="modal fade" id="editGoalPillarModal{{ $goalPillar->id }}" tabindex="-1" aria-labelledby="editGoalPillarModalLabel{{ $goalPillar->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form method="post" action="{{ route('goal-pillars.update', $goalPillar) }}" class="modal-content">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title h5 fw-bold" id="editGoalPillarModalLabel{{ $goalPillar->id }}">Edit Goal Pillar</h2>
                            <div class="text-muted small">Update the strategic pillar details.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label fw-semibold">Pillar Name</label>
                        <input class="form-control mb-3" name="name" value="{{ old('name', $goalPillar->name) }}" required>

                        <label class="form-label fw-semibold">Annual Goal</label>
                        <textarea class="form-control mb-3" name="annual_goal" rows="3" required>{{ old('annual_goal', $goalPillar->annual_goal) }}</textarea>

                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control mb-3" name="description" rows="3">{{ old('description', $goalPillar->description) }}</textarea>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sort Order</label>
                                <input class="form-control" type="number" min="0" max="65535" name="sort_order" value="{{ old('sort_order', $goalPillar->sort_order) }}" required>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $goalPillar->is_active))>
                                    <span class="form-check-label">Active</span>
                                </label>
                            </div>
                        </div>
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
