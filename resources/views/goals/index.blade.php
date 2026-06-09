<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Goals</h1>
    </x-slot>

    <style>
        .goal-panel,
        .goal-filter {
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

        .progress {
            height: .65rem;
            border-radius: 999px;
        }

        .progress-bar {
            background: linear-gradient(90deg, #8f171b, #c2363d);
        }
    </style>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
        <div>
            <h2 class="h5 fw-bold mb-1">All Visible Goals</h2>
            <div class="text-muted small">Goals filtered by your department, unit, and role access.</div>
        </div>
        @if ($canCreateGoals)
            <a class="btn btn-maroon" href="{{ route('goals.create') }}">Create Goal</a>
        @endif
    </div>

    <form method="get" action="{{ route('goals.index') }}" class="goal-filter p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-lg-4">
                <label class="form-label small fw-semibold">Search</label>
                <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Goal title or description">
            </div>
            <div class="col-lg-2">
                <label class="form-label small fw-semibold">Quarter</label>
                <select class="form-select" name="quarter_id">
                    <option value="">All</option>
                    @foreach ($quarters as $quarter)
                        <option value="{{ $quarter->id }}" @selected(request('quarter_id') == $quarter->id)>{{ $quarter->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label small fw-semibold">Status</label>
                <select class="form-select" name="status">
                    <option value="">All</option>
                    @foreach (['draft', 'submitted', 'approved', 'in_progress', 'completed', 'archived'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label small fw-semibold">Department</label>
                <select class="form-select" name="department_id">
                    <option value="">All</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 d-grid">
                <button class="btn btn-maroon">Filter</button>
            </div>
        </div>
    </form>

    <div class="goal-panel table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Main Goal</th>
                    <th>Quarter</th>
                    <th>Scope</th>
                    <th>Objectives</th>
                    <th style="width: 190px">Progress</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($goals as $goal)
                <tr>
                    <td>
                        <strong>{{ $goal->title }}</strong>
                        <br>
                        <small class="text-muted">{{ $goal->department->name }}</small>
                    </td>
                    <td>{{ $goal->quarter->name }}</td>
                    <td>{{ $goal->unit?->name ?? 'Department-wide' }}</td>
                    <td>
                        <span class="badge text-bg-light border">{{ $goal->objectives->count() }} total</span>
                        <span class="badge text-bg-success">{{ $goal->objectives->where('status', 'completed')->count() }} completed</span>
                    </td>
                    <td>
                        <div class="progress"><div class="progress-bar" style="width: {{ $goal->progress() }}%"></div></div>
                        <small class="text-muted">{{ $goal->progress() }}% from completed approved objectives</small>
                    </td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('goals.show', $goal) }}">Open</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-4 text-muted">No visible goals match your filters.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $goals->links() }}
    </div>
</x-app-layout>
