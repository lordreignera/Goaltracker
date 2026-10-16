@php
    $planningContext = $planningContext ?? 'dashboard';
    $canAddGoals = $canAddGoals ?? false;
    $manageableGoalIds = collect($manageableGoalIds ?? []);
    $reportableGoalIds = collect($reportableGoalIds ?? []);
@endphp

@once
    <style>
        .pillar-overview-wrap { overflow: visible !important; }
        .pillar-overview-table { width: 100%; min-width: 0 !important; table-layout: fixed; }
        .pillar-overview-table th:nth-child(1) { width: 15%; }
        .pillar-overview-table th:nth-child(2) { width: 22%; }
        .pillar-overview-table th:nth-child(3) { width: 23%; }
        .pillar-overview-table th:nth-child(4) { width: 14%; }
        .pillar-overview-table th:nth-child(5) { width: 16%; }
        .pillar-overview-table th:nth-child(6) { width: 10%; }
        .pillar-overview-table td,
        .pillar-overview-table th { overflow-wrap: anywhere; vertical-align: top; }
        .pillar-goal-actions { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
        .pillar-activity-list { margin: 0; padding-left: 18px; }
        .pillar-progress-line {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 5px;
            font-size: .78rem;
            color: var(--sg-muted);
        }

        @media (max-width: 991.98px) {
            .pillar-overview-table,
            .pillar-overview-table tbody,
            .pillar-overview-table tr,
            .pillar-overview-table td { display: block; width: 100%; }
            .pillar-overview-table thead { display: none; }
            .pillar-overview-table tr { border-bottom: 8px solid var(--sg-bg); }
            .pillar-overview-table td {
                display: grid;
                grid-template-columns: minmax(120px, 28%) 1fr;
                gap: 12px;
                border-bottom: 1px solid var(--sg-border);
            }
            .pillar-overview-table td::before {
                content: attr(data-label);
                color: var(--sg-muted);
                font-size: .72rem;
                font-weight: 800;
                text-transform: uppercase;
            }
        }

        @media (max-width: 575.98px) {
            .pillar-overview-table td { grid-template-columns: 1fr; gap: 5px; }
        }
    </style>
@endonce

<div class="table-responsive pillar-overview-wrap">
    <table class="table table-sm pillar-overview-table align-middle mb-0">
        <thead>
            <tr>
                <th>Activity Pillar</th>
                <th>Work Plan Goal</th>
                <th>Activities & Deliverable</th>
                <th>Timeline</th>
                <th>Reporting & Progress</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($goalPillars as $goalPillar)
                @php
                    $pillarGoals = $goalsByPillar->get($goalPillar->id, collect());
                    $pillarRows = $pillarGoals->flatMap(function ($goal) {
                        return $goal->objectives->map(fn ($objective) => [$goal, $objective]);
                    });
                @endphp

                @forelse ($pillarRows->take(1) as $row)
                    @php
                        $goal = $row[0];
                        $objective = $row[1];
                        $keyActivities = $objective->keyActivitiesList();
                        $latestUpdate = $objective->relationLoaded('latestWeeklyUpdate')
                            ? $objective->latestWeeklyUpdate
                            : $objective->weeklyUpdates->sortByDesc('report_date')->first();
                        $latestReview = $latestUpdate?->reviews?->sortByDesc('created_at')->first();
                        $reportCount = $objective->relationLoaded('weeklyUpdates')
                            ? $objective->weeklyUpdates->count()
                            : ($objective->weekly_updates_count ?? 0);
                        $canManageGoal = $manageableGoalIds->contains($goal->id);
                        $canReport = $reportableGoalIds->contains($goal->id);
                    @endphp
                    <tr>
                        <td data-label="Activity Pillar">
                            <div class="fw-bold">{{ $goalPillar->name }}</div>
                            @if ($goalPillar->annual_goal)
                                <div class="text-muted small mt-1">{{ str($goalPillar->annual_goal)->limit(110) }}</div>
                            @endif
                            @if ($canAddGoals && $planningContext === 'create')
                                <button class="btn btn-sm btn-outline-secondary mt-2" type="button" data-add-for-pillar data-pillar-id="{{ $goalPillar->id }}" data-pillar-name="{{ $goalPillar->name }}">Add Goal</button>
                            @endif
                        </td>
                        <td data-label="Work Plan Goal">
                            <div class="fw-bold">{{ $objective->title }}</div>
                            <div class="text-muted small mt-1">{{ $goal->assignedDepartments->pluck('name')->join(', ') }}</div>
                            <button class="btn btn-sm btn-outline-secondary mt-2" type="button" data-bs-toggle="modal" data-bs-target="#pillarObjectivesModal-{{ $goalPillar->id }}">
                                View all objectives ({{ $pillarRows->count() }})
                            </button>
                            @if ($goal->status === 'draft')
                                <span class="badge text-bg-warning border mt-1">Draft</span>
                                <div class="text-muted small mt-1">Complete and submit this goal before reporting.</div>
                            @endif
                            <div class="pillar-goal-actions">
                                @if ($canReport)
                                    @if ($planningContext === 'create')
                                        <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#reportObjectiveModal-{{ $objective->id }}">Add Report</button>
                                    @else
                                        <a class="btn btn-sm btn-primary" href="{{ route('goals.show', $goal) }}#report-objective-{{ $objective->id }}">Add Report</a>
                                    @endif
                                @endif

                                @if ($planningContext === 'create')
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#viewObjectiveModal-{{ $objective->id }}">View</button>
                                @else
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('goals.show', $goal) }}">Open</a>
                                @endif
                            </div>
                        </td>
                        <td data-label="Activities & Deliverable">
                            <ul class="pillar-activity-list small">
                                @foreach (collect($keyActivities)->take(2) as $activity)
                                    <li>{{ $activity }}</li>
                                @endforeach
                            </ul>
                            @if (count($keyActivities) > 2)
                                <details class="small mt-1">
                                    <summary>{{ count($keyActivities) - 2 }} more activit{{ count($keyActivities) - 2 === 1 ? 'y' : 'ies' }}</summary>
                                    <ul class="pillar-activity-list mt-1">
                                        @foreach (collect($keyActivities)->skip(2) as $activity)
                                            <li>{{ $activity }}</li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                            <div class="small mt-2"><strong>Deliverable:</strong> {{ str($objective->specific_output)->limit(100) }}</div>
                        </td>
                        <td data-label="Timeline">
                            <div class="fw-semibold small">{{ $goal->quarter?->name }}</div>
                            <div class="text-muted small mt-1">{{ $objective->starts_at?->format('M d, Y') }}</div>
                            <div class="text-muted small">to {{ $objective->due_at?->format('M d, Y') }}</div>
                            <span class="badge text-bg-light border mt-2">{{ $objective->weight }}% weight</span>
                        </td>
                        <td data-label="Reporting & Progress">
                            <div class="pillar-progress-line">
                                <span>Verified progress</span>
                                <strong>{{ $objective->progressPercent() }}%</strong>
                            </div>
                            <div class="progress" style="height: 7px;">
                                <div class="progress-bar" style="width: {{ $objective->progressPercent() }}%"></div>
                            </div>
                            <div class="mt-2">
                                @foreach ($objective->reportingFrequencies() as $frequency)
                                    <span class="badge text-bg-light border">{{ ucfirst($frequency) }}</span>
                                @endforeach
                            </div>
                            <div class="text-muted small mt-2">{{ $reportCount }} report{{ $reportCount === 1 ? '' : 's' }}</div>
                            <div class="small mt-1">{{ $latestUpdate?->action_points ? str($latestUpdate->action_points)->limit(55) : 'No next step yet' }}</div>
                            <div class="text-muted small mt-1">{{ $latestReview?->comments ? str($latestReview->comments)->limit(55) : 'No supervisor review yet' }}</div>
                            @if ($planningContext === 'create')
                                <button class="btn btn-sm btn-outline-secondary mt-2" type="button" data-bs-toggle="modal" data-bs-target="#pillarReportsModal-{{ $goalPillar->id }}">View Reports</button>
                            @endif
                        </td>
                        <td data-label="Actions">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @if ($canAddGoals)
                                        <li>
                                            <button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#dashboardCreateGoalModal" data-dashboard-add-for-pillar data-pillar-id="{{ $goalPillar->id }}">Add Work Goal</button>
                                        </li>
                                    @endif
                                    @if ($canManageGoal)
                                        <li><a class="dropdown-item" href="{{ route('goals.edit', $goal) }}">Edit</a></li>
                                        @if ($goal->status === 'draft')
                                            <li>
                                                <form method="post" action="{{ route('goals.submit', $goal) }}">
                                                    @csrf
                                                    <button class="dropdown-item" type="submit">Submit Goal</button>
                                                </form>
                                            </li>
                                        @endif
                                        <li>
                                            <form method="post" action="{{ route('goals.destroy', $goal) }}" onsubmit="return confirm('Delete this work plan? This cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="modal_source" value="delete_goal">
                                                <button class="dropdown-item text-danger" type="submit">Delete</button>
                                            </form>
                                        </li>
                                    @endif
                                    @if ($canReport)
                                        <li><a class="dropdown-item" href="{{ route('goals.show', $goal) }}#report-objective-{{ $objective->id }}">Add Report</a></li>
                                    @endif
                                    <li><a class="dropdown-item" href="{{ route('goals.show', $goal) }}#report-objective-{{ $objective->id }}">View Progress</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td data-label="Activity Pillar">
                            <div class="fw-bold">{{ $goalPillar->name }}</div>
                            @if ($goalPillar->annual_goal)
                                <div class="text-muted small mt-1">{{ str($goalPillar->annual_goal)->limit(110) }}</div>
                            @endif
                        </td>
                        <td colspan="4" data-label="Work Plan Goals">
                            <div class="text-muted small">No work plan goals recorded under this pillar yet.</div>
                            @if ($canAddGoals && $planningContext === 'create')
                                <button class="btn btn-sm btn-primary mt-2" type="button" data-add-for-pillar data-pillar-id="{{ $goalPillar->id }}" data-pillar-name="{{ $goalPillar->name }}">Add Work Plan Goal</button>
                            @endif
                        </td>
                        <td data-label="Actions">
                            @if ($canAddGoals && $planningContext === 'dashboard')
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#dashboardCreateGoalModal" data-dashboard-add-for-pillar data-pillar-id="{{ $goalPillar->id }}">Add Work Goal</button>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforelse
            @endforeach
        </tbody>
    </table>
</div>

@foreach ($goalPillars as $goalPillar)
    @php
        $pillarGoals = $goalsByPillar->get($goalPillar->id, collect());
        $pillarRows = $pillarGoals->flatMap(function ($goal) {
            return $goal->objectives->map(fn ($objective) => [$goal, $objective]);
        });
    @endphp

    @if ($pillarRows->isNotEmpty())
        @include('goals.partials.pillar-objectives-modal', [
            'goalPillar' => $goalPillar,
            'pillarRows' => $pillarRows,
            'planningContext' => $planningContext,
            'manageableGoalIds' => $manageableGoalIds,
            'reportableGoalIds' => $reportableGoalIds,
        ])
    @endif
@endforeach
