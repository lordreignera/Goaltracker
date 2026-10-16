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

        .progress {
            height: .65rem;
            border-radius: 999px;
        }

        .progress-bar {
            background: var(--sg-blue);
        }
    </style>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
        <div>
            <h2 class="h5 fw-bold mb-1">All Visible Goals</h2>
            <div class="text-muted small">Goals filtered by your department, section, and role access.</div>
        </div>
        @if ($canCreateGoals)
            <a class="btn btn-maroon" href="{{ route('goals.create') }}">Create Goal</a>
        @endif
    </div>

    <form method="get" action="{{ route('goals.index') }}" class="goal-filter p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-lg-4">
                <label class="form-label small fw-semibold">Search</label>
                <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Goal title, metric, or SMART detail">
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
                <label class="form-label small fw-semibold">Goal Pillar</label>
                <select class="form-select" name="goal_pillar_id">
                    <option value="">All</option>
                    @foreach ($goalPillars as $goalPillar)
                        <option value="{{ $goalPillar->id }}" @selected(request('goal_pillar_id') == $goalPillar->id)>{{ $goalPillar->name }}</option>
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
                    <th>Office / Department</th>
                    <th>Goal Pillar</th>
                    <th>Quarter</th>
                    <th>Status</th>
                    <th>Work Plan Goals</th>
                    <th style="width: 190px">Progress</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($goals as $goal)
                <tr>
                    <td>
                        <strong>{{ $goal->assignedDepartments->pluck('name')->join(', ') ?: 'Unassigned office' }}</strong>
                    </td>
                    <td>{{ $goal->pillar?->name ?? 'Unassigned' }}</td>
                    <td>{{ $goal->quarter->name }}</td>
                    <td><span class="badge text-bg-light border">{{ str_replace('_', ' ', ucfirst($goal->status)) }}</span></td>
                    <td>
                        @php($previewObjective = $goal->objectives->first())
                        <span class="badge text-bg-light border">{{ $goal->objectives->count() }} total</span>
                        <span class="badge text-bg-success">{{ $goal->objectives->filter->isApprovedComplete()->count() }} completed</span>
                        @if ($previewObjective)
                            <div class="small fw-semibold mt-1">{{ $previewObjective->title }}</div>
                            <button class="btn btn-sm btn-outline-secondary mt-2" type="button" data-bs-toggle="modal" data-bs-target="#goalObjectivesModal-{{ $goal->id }}">
                                View all objectives
                            </button>
                        @endif
                    </td>
                    <td>
                        <div class="progress"><div class="progress-bar" style="width: {{ $goal->progress() }}%"></div></div>
                        <small class="text-muted">{{ $goal->progress() }}% from approved progress updates</small>
                    </td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('goals.show', $goal) }}">Open</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-4 text-muted">No visible goals match your filters.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @foreach ($goals as $goal)
        <div class="modal fade" id="goalObjectivesModal-{{ $goal->id }}" tabindex="-1" aria-labelledby="goalObjectivesModalLabel-{{ $goal->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h3 class="modal-title h5 fw-bold mb-1" id="goalObjectivesModalLabel-{{ $goal->id }}">{{ $goal->pillar?->name ?? 'Work Plan' }} Objectives</h3>
                            <div class="text-muted small">{{ $goal->assignedDepartments->pluck('name')->join(', ') ?: 'Office-wide' }} | {{ $goal->quarter->name }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="list-group list-group-flush">
                            @foreach ($goal->objectives as $objective)
                                <div class="list-group-item px-0">
                                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                                        <div>
                                            <div class="fw-semibold">{{ $objective->title }}</div>
                                            <div class="text-muted small mt-1">{{ str($objective->key_activities)->limit(120) }}</div>
                                            <div class="small mt-1"><strong>Deliverable:</strong> {{ str($objective->specific_output)->limit(120) }}</div>
                                        </div>
                                        <div class="text-md-end">
                                            <span class="badge text-bg-light border">{{ $objective->weight }}% weight</span>
                                            <div class="text-muted small mt-1">{{ $objective->progressPercent() }}% verified</div>
                                            <a class="btn btn-sm btn-outline-secondary mt-2" href="{{ route('goals.show', $goal) }}#report-objective-{{ $objective->id }}">Open progress</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-primary" href="{{ route('goals.show', $goal) }}">Open Goal</a>
                        <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="mt-3">
        {{ $goals->links() }}
    </div>
</x-app-layout>
