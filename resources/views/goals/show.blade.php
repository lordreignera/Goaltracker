<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">{{ $goal->title }}</h1>
    </x-slot>

    <style>
        .goal-summary-actions {
            min-width: 180px;
        }

        @media (max-width: 767.98px) {
            .goal-summary {
                flex-direction: column;
            }

            .goal-summary-actions {
                width: 100%;
                min-width: 0;
                text-align: left !important;
            }

            .review-form {
                align-items: stretch;
            }
        }
    </style>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="bg-white border rounded-3 p-4 mb-4">
                <div class="goal-summary d-flex justify-content-between gap-3">
                    <div>
                        <div class="text-muted">{{ $goal->quarter->name }} / {{ $goal->department->name }} / {{ $goal->unit?->name ?? 'Department-wide' }}</div>
                        <p class="mb-0 mt-2">{{ $goal->description }}</p>
                    </div>
                    <div class="goal-summary-actions text-end">
                        <div class="h2 mb-1">{{ $goal->progress() }}%</div>
                        <small class="text-muted d-block mb-2">Completed approved objective weight</small>
                        <div class="progress"><div class="progress-bar bg-success" style="width: {{ $goal->progress() }}%"></div></div>
                        @if ($canUpdateGoal)
                        <form method="post" action="{{ route('goals.submit', $goal) }}" class="mt-3">
                            @csrf
                            <button class="btn btn-sm btn-outline-success">Submit Goal</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>

            @foreach ($goal->objectives as $objective)
                <div class="bg-white border rounded-3 p-4 mb-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                        <div>
                            <h2 class="h5 mb-1">{{ $objective->title }}</h2>
                            <p class="text-muted mb-0">{{ $objective->description }}</p>
                        </div>
                        <span class="badge text-bg-{{ $objective->status === 'completed' ? 'success' : 'secondary' }} align-self-start">
                            {{ str_replace('_', ' ', $objective->status) }} / {{ $objective->weight }}%
                        </span>
                    </div>

                    <form method="post" action="{{ route('objectives.weekly-updates.store', $objective) }}" class="border rounded-3 p-3 mb-3">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-3"><input class="form-control" type="number" min="1" max="13" name="week_number" placeholder="Week" required></div>
                            <div class="col-md-4"><input class="form-control" type="date" name="week_starting"></div>
                            <div class="col-md-5"><input class="form-control" type="number" min="0" max="100" name="percentage_estimate" placeholder="Estimate %" required></div>
                            <div class="col-12"><textarea class="form-control" name="progress_summary" placeholder="Progress summary" required></textarea></div>
                            <div class="col-md-4"><textarea class="form-control" name="achievements" placeholder="Achievements"></textarea></div>
                            <div class="col-md-4"><textarea class="form-control" name="challenges" placeholder="Challenges"></textarea></div>
                            <div class="col-md-4"><textarea class="form-control" name="next_actions" placeholder="Next actions"></textarea></div>
                        </div>
                        <button class="btn btn-sm btn-primary mt-3">Submit Weekly Update</button>
                    </form>

                    @foreach ($objective->weeklyUpdates as $update)
                        <div class="border-top py-3">
                            <div class="d-flex justify-content-between">
                                <strong>Week {{ $update->week_number }}</strong>
                                <span class="badge text-bg-light border">{{ str_replace('_', ' ', $update->status) }}</span>
                            </div>
                            <p class="mb-2">{{ $update->progress_summary }}</p>
                            <div class="row small text-muted">
                                <div class="col-md-4"><strong>Achievements:</strong> {{ $update->achievements ?: 'None provided' }}</div>
                                <div class="col-md-4"><strong>Challenges:</strong> {{ $update->challenges ?: 'None provided' }}</div>
                                <div class="col-md-4"><strong>Next:</strong> {{ $update->next_actions ?: 'None provided' }}</div>
                            </div>
                            @if ($canReviewGoal)
                            <form method="post" action="{{ route('weekly-updates.reviews.store', $update) }}" class="review-form row g-2 mt-2">
                                @csrf
                                <div class="col-md-3">
                                    <select class="form-select form-select-sm" name="decision" required>
                                        <option value="approved">Approve as completed</option>
                                        <option value="rejected">Reject</option>
                                        <option value="revision_requested">Request revision</option>
                                    </select>
                                </div>
                                <div class="col-md-7"><input class="form-control form-control-sm" name="comments" placeholder="Supervisor comments"></div>
                                <div class="col-md-2"><button class="btn btn-sm btn-outline-success w-100">Review</button></div>
                            </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        @if ($canUpdateGoal)
        <div class="col-xl-4">
            <form method="post" action="{{ route('goals.objectives.store', $goal) }}" class="bg-white border rounded-3 p-4">
                @csrf
                <h2 class="h5 fw-bold">Add Objective</h2>
                <input class="form-control mb-2" name="title" placeholder="Objective title" required>
                <textarea class="form-control mb-2" name="description" placeholder="Specific, measurable objective"></textarea>
                <input class="form-control mb-2" type="number" min="1" max="100" name="weight" placeholder="Weight %" required>
                <input class="form-control mb-3" type="date" name="due_at">
                <button class="btn btn-success w-100">Add Objective</button>
                <small class="text-muted d-block mt-2">Total objective weights should equal 100%. Only objectives completed by staff and approved by a supervisor contribute to main goal progress.</small>
            </form>
        </div>
        @endif
    </div>
</x-app-layout>
