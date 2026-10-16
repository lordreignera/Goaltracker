@php
    $planningContext = $planningContext ?? 'dashboard';
    $manageableGoalIds = collect($manageableGoalIds ?? []);
    $reportableGoalIds = collect($reportableGoalIds ?? []);
@endphp

<div class="modal fade" id="pillarObjectivesModal-{{ $goalPillar->id }}" tabindex="-1" aria-labelledby="pillarObjectivesModalLabel-{{ $goalPillar->id }}" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title h5 fw-bold mb-1" id="pillarObjectivesModalLabel-{{ $goalPillar->id }}">{{ $goalPillar->name }} Objectives</h3>
                    <div class="text-muted small">{{ $pillarRows->count() }} objective{{ $pillarRows->count() === 1 ? '' : 's' }} across this activity pillar.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Work Plan Goal</th>
                                <th>Activities & Deliverable</th>
                                <th>Timeline</th>
                                <th>Reporting & Progress</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($pillarRows as $row)
                            @php
                                $goal = $row[0];
                                $objective = $row[1];
                                $canManageGoal = $manageableGoalIds->contains($goal->id);
                                $canReport = $reportableGoalIds->contains($goal->id);
                                $reportCount = $objective->relationLoaded('weeklyUpdates')
                                    ? $objective->weeklyUpdates->count()
                                    : ($objective->weekly_updates_count ?? 0);
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $objective->title }}</div>
                                    <div class="text-muted small">{{ $goal->assignedDepartments->pluck('name')->join(', ') ?: 'Office-wide' }}</div>
                                    @if ($goal->status === 'draft')
                                        <span class="badge text-bg-warning border mt-1">Draft</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="small">{{ str($objective->key_activities)->limit(115) }}</div>
                                    <div class="small mt-1"><strong>Deliverable:</strong> {{ str($objective->specific_output)->limit(115) }}</div>
                                </td>
                                <td>
                                    <div class="small">{{ $goal->quarter?->name }}</div>
                                    <div class="text-muted small">{{ $objective->starts_at?->format('M d, Y') }} to {{ $objective->due_at?->format('M d, Y') }}</div>
                                    <span class="badge text-bg-light border mt-1">{{ $objective->weight }}% weight</span>
                                </td>
                                <td>
                                    <div class="small">{{ collect($objective->reportingFrequencies())->map(fn ($frequency) => ucfirst($frequency))->join(', ') }}</div>
                                    <div class="text-muted small mt-1">{{ $objective->progressPercent() }}% verified · {{ $reportCount }} report{{ $reportCount === 1 ? '' : 's' }}</div>
                                </td>
                                <td class="text-end">
                                    @if ($planningContext === 'create')
                                        <div class="d-flex flex-wrap justify-content-end gap-1">
                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#viewObjectiveModal-{{ $objective->id }}">View</button>
                                            @if ($canReport)
                                                <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#reportObjectiveModal-{{ $objective->id }}">Add Report</button>
                                            @endif
                                        </div>
                                    @else
                                        <div class="d-flex flex-wrap justify-content-end gap-1">
                                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('goals.show', $goal) }}">Open</a>
                                            @if ($canReport)
                                                <a class="btn btn-sm btn-primary" href="{{ route('goals.show', $goal) }}#report-objective-{{ $objective->id }}">Add Report</a>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
