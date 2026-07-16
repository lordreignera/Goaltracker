<x-app-layout>
    <style>
        .dashboard-grid {
            display: grid;
            gap: 18px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .summary-card,
        .dashboard-card {
            background: var(--sg-surface);
            border: 1px solid var(--sg-border);
            border-radius: 14px;
            box-shadow: var(--sg-shadow);
        }

        .summary-card {
            min-height: 150px;
            padding: 22px;
        }

        .summary-icon {
            width: 44px;
            height: 44px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .summary-icon svg {
            width: 22px;
            height: 22px;
        }

        .summary-value {
            color: var(--sg-text);
            font-size: 2rem;
            line-height: 1;
            font-weight: 800;
            margin-top: 12px;
        }

        .summary-title {
            color: var(--sg-text);
            font-weight: 800;
            font-size: .9rem;
        }

        .summary-support,
        .summary-compare {
            color: var(--sg-muted);
            font-size: .78rem;
        }

        .summary-compare strong {
            font-weight: 800;
        }

        .soft-blue {
            background: #dbeafe;
            color: var(--sg-blue);
        }

        .soft-success {
            background: #d1fae5;
            color: var(--sg-success);
        }

        .soft-warning {
            background: #fef3c7;
            color: var(--sg-warning);
        }

        .soft-danger {
            background: #fee2e2;
            color: var(--sg-danger);
        }

        .progress-ring {
            width: 188px;
            aspect-ratio: 1;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at center, var(--sg-surface) 0 58%, transparent 59%),
                conic-gradient(var(--sg-blue) calc(var(--value) * 1%), #e0e7ff 0);
        }

        .progress-ring-value {
            font-size: 2.1rem;
            font-weight: 800;
            color: var(--sg-navy);
        }

        .bar-row {
            display: grid;
            grid-template-columns: minmax(120px, 1fr) minmax(120px, 2fr) 48px;
            align-items: center;
            gap: 12px;
            font-size: .84rem;
        }

        .bar-track {
            height: 8px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            border-radius: 999px;
            background: var(--sg-blue);
        }

        .task-dot {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }

        .mini-icon {
            width: 18px;
            height: 18px;
        }

        @media (max-width: 1199.98px) {
            .summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }

            .summary-card {
                min-height: 128px;
                padding: 18px;
            }

            .bar-row {
                grid-template-columns: 1fr;
                gap: 6px;
            }
        }
    </style>

    @php
        $icon = function (string $name): string {
            return match ($name) {
                'target' => '<svg class="mini-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3"/><path d="m15 9 5-5m-1 0h-4v4"/></svg>',
                'check' => '<svg class="mini-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg>',
                'clock' => '<svg class="mini-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
                'x' => '<svg class="mini-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m9 9 6 6m0-6-6 6"/></svg>',
                'report' => '<svg class="mini-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V5m0 14h16M8 16v-5m4 5V8m4 8v-7"/></svg>',
                default => '',
            };
        };
        $total = max((int) $totalGoals, 1);
        $goalRows = $goals->take(5);
        $dashboardScore = $organizationScore ?? $averageProgress;
        $dashboardScoreLabel = $organizationScore !== null ? 'Organization Score' : 'Overall Progress';
    @endphp

    <div class="dashboard-grid">
        <section class="summary-grid">
            <article class="summary-card">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <div class="summary-title">Total Goals</div>
                        <div class="summary-value">{{ $totalGoals }}</div>
                    </div>
                    <span class="summary-icon soft-blue">{!! $icon('target') !!}</span>
                </div>
                <div class="summary-support mt-3">Across visible departments</div>
                <div class="summary-compare mt-3"><strong class="text-success">0%</strong> vs last quarter</div>
            </article>

            <article class="summary-card">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <div class="summary-title">On Track</div>
                        <div class="summary-value">{{ $onTrackGoals }}</div>
                    </div>
                    <span class="summary-icon soft-success">{!! $icon('check') !!}</span>
                </div>
                <div class="summary-support mt-3">{{ round(($onTrackGoals / $total) * 100, 1) }}% of total goals</div>
                <div class="summary-compare mt-3"><strong class="text-success">0%</strong> vs last quarter</div>
            </article>

            <article class="summary-card">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <div class="summary-title">At Risk</div>
                        <div class="summary-value">{{ $atRiskGoals }}</div>
                    </div>
                    <span class="summary-icon soft-warning">{!! $icon('clock') !!}</span>
                </div>
                <div class="summary-support mt-3">{{ round(($atRiskGoals / $total) * 100, 1) }}% of total goals</div>
                <div class="summary-compare mt-3"><strong class="text-warning">0%</strong> vs last quarter</div>
            </article>

            <article class="summary-card">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <div class="summary-title">Off Track</div>
                        <div class="summary-value">{{ $offTrackGoals }}</div>
                    </div>
                    <span class="summary-icon soft-danger">{!! $icon('x') !!}</span>
                </div>
                <div class="summary-support mt-3">{{ round(($offTrackGoals / $total) * 100, 1) }}% of total goals</div>
                <div class="summary-compare mt-3"><strong class="text-danger">0%</strong> vs last quarter</div>
            </article>
        </section>

        <div class="row g-3">
            <div class="col-xl-8">
                <section class="dashboard-card h-100 p-4">
                    <div class="d-flex flex-column flex-md-row align-items-center gap-4">
                        <div class="progress-ring" style="--value: {{ $dashboardScore }}">
                            <div class="text-center">
                                <div class="progress-ring-value">{{ $dashboardScore }}%</div>
                                <div class="text-muted small">{{ $dashboardScoreLabel }}</div>
                            </div>
                        </div>

                        <div class="flex-grow-1 w-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h2 class="h6 fw-bold mb-1">Goal Progress</h2>
                                    <div class="text-muted small">Latest supervisor-verified progress by visible goal.</div>
                                </div>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('reports.quarterly.index') }}">View report</a>
                            </div>

                            <div class="d-grid gap-3">
                                @forelse ($goalRows as $goal)
                                    <div class="bar-row">
                                        <div class="fw-semibold text-truncate">{{ $goal->title }}</div>
                                        <div class="bar-track">
                                            <div class="bar-fill" style="width: {{ $goal->progress() }}%"></div>
                                        </div>
                                        <div class="text-muted text-end">{{ $goal->progress() }}%</div>
                                    </div>
                                @empty
                                    <div class="text-muted">No goals are visible for your department, section, or unit yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-xl-4">
                <section class="dashboard-card p-4 mb-3">
                    <div class="d-flex gap-3">
                        <span class="task-dot soft-blue">{!! $icon('report') !!}</span>
                        <div>
                            <h2 class="h6 fw-bold mb-1">New here?</h2>
                            <div class="text-muted small mb-3">Read the user guide to understand goal creation, reporting, supervisor verification, and progress scoring.</div>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-sm btn-primary" href="{{ route('help.user-guide') }}">Open Guide</a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('help.user-guide.pdf') }}">Download PDF</a>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="dashboard-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h6 fw-bold mb-0">My Tasks</h2>
                        <a class="small fw-bold text-decoration-none" href="{{ route('goals.index') }}">View all</a>
                    </div>

                    <div class="d-grid gap-3">
                        <div class="d-flex gap-3">
                            <span class="task-dot soft-warning">{!! $icon('clock') !!}</span>
                            <div>
                                <div class="fw-bold small">Review pending reports</div>
                                <div class="text-muted small">{{ $pendingReviews }} report{{ $pendingReviews === 1 ? '' : 's' }} awaiting supervisor action</div>
                            </div>
                        </div>
                        <div class="d-flex gap-3">
                            <span class="task-dot soft-blue">{!! $icon('target') !!}</span>
                            <div>
                                <div class="fw-bold small">Manage active goals</div>
                                <div class="text-muted small">{{ $activeGoals }} active goal{{ $activeGoals === 1 ? '' : 's' }}</div>
                            </div>
                        </div>
                        <div class="d-flex gap-3">
                            <span class="task-dot soft-success">{!! $icon('check') !!}</span>
                            <div>
                                <div class="fw-bold small">Completed goals</div>
                                <div class="text-muted small">{{ $completedGoals }} completed this cycle</div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <section class="dashboard-card">
            <div class="p-4 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
                <div>
                    <h2 class="h6 mb-1 fw-bold">Recent Goal Activity</h2>
                    <div class="text-muted small">Goals filtered by your department, section, unit, and role access.</div>
                </div>
                <a class="btn btn-sm btn-primary" href="{{ route('goals.index') }}">Manage Goals</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Goal</th>
                            <th>Status</th>
                            <th style="width: 34%">Progress</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($goals as $goal)
                        <tr>
                            <td class="fw-bold">{{ $goal->title }}</td>
                            <td><span class="badge text-bg-light border">{{ str_replace('_', ' ', $goal->status) }}</span></td>
                            <td>
                                <div class="progress" style="height: 8px;"><div class="progress-bar" style="width: {{ $goal->progress() }}%"></div></div>
                                <small class="text-muted">{{ $goal->progress() }}%</small>
                            </td>
                            <td class="text-end"><a href="{{ route('goals.show', $goal) }}" class="btn btn-sm btn-outline-secondary">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted p-4">No goals are visible for your department, section, or unit yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
