<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Organization Dashboard</h1>
    </x-slot>

    <style>
        .dashboard-hero {
            border-radius: 16px;
            background: linear-gradient(135deg, #ffffff, #e5e7eb);
            color: #111827;
            border: 1px solid #d1d5db;
            padding: 28px;
            overflow: hidden;
        }

        .dashboard-card {
            border: 1px solid #e6e9ef;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 10px 28px rgba(20, 24, 31, .04);
        }

        .stat-card {
            min-height: 138px;
            padding: 20px;
            border-radius: 14px;
            background: #fff;
            border: 1px solid #e6e9ef;
            box-shadow: 0 10px 28px rgba(20, 24, 31, .04);
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            color: #111318;
            font-weight: 900;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }

        .progress {
            height: .65rem;
            border-radius: 999px;
        }

        .progress-bar {
            background: linear-gradient(90deg, #000000, #4b5563);
        }

        @media (max-width: 575.98px) {
            .dashboard-hero {
                padding: 20px;
                border-radius: 12px;
            }

            .dashboard-hero .display-6 {
                font-size: 1.75rem;
            }

            .dashboard-hero .lead {
                font-size: 1rem;
            }

            .stat-card {
                min-height: 112px;
                padding: 16px;
            }

            .stat-value {
                font-size: 1.7rem;
            }
        }
    </style>

    <div class="dashboard-hero mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge text-bg-light mb-3">90-day accountability cycle</span>
                <h2 class="display-6 fw-bold mb-3">Track progress from staff goals to organization performance.</h2>
                <p class="lead mb-0 opacity-75">Review active goals, pending approvals, and measurable progress across departments, sections, and units.</p>
            </div>
            <div class="col-lg-4">
                <div class="p-4 rounded-4" style="background: rgba(255,255,255,.13); border: 1px solid rgba(255,255,255,.22);">
                    <div class="small opacity-75">Organization Score</div>
                    <div class="display-5 fw-bold">{{ $organizationScore ?? $averageProgress }}%</div>
                    <div class="progress mt-3" style="background: rgba(255,255,255,.28);">
                        <div class="progress-bar" style="width: {{ $organizationScore ?? $averageProgress }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div><div class="text-muted small">Performance</div><div class="stat-value mt-2">{{ $averageProgress }}%</div></div>
                    <span class="stat-icon">%</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div><div class="text-muted small">Active Goals</div><div class="stat-value mt-2">{{ $activeGoals }}</div></div>
                    <span class="stat-icon">G</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div><div class="text-muted small">Completed Goals</div><div class="stat-value mt-2">{{ $completedGoals }}</div></div>
                    <span class="stat-icon">C</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div><div class="text-muted small">Pending Reviews</div><div class="stat-value mt-2">{{ $pendingReviews }}</div></div>
                    <span class="stat-icon">R</span>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="p-4 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
            <div>
                <h2 class="h5 mb-1 fw-bold">Visible Goals</h2>
                <div class="text-muted small">Goals filtered by your department, section, unit, and role access.</div>
            </div>
            <a class="btn btn-sm btn-maroon" href="{{ route('goals.index') }}">Manage Goals</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr><th>Goal</th><th>Status</th><th style="width: 34%">Progress</th><th></th></tr></thead>
                <tbody>
                @forelse ($goals as $goal)
                    <tr>
                        <td class="fw-bold">{{ $goal->title }}</td>
                        <td><span class="badge text-bg-secondary">{{ str_replace('_', ' ', $goal->status) }}</span></td>
                        <td>
                            <div class="progress"><div class="progress-bar" style="width: {{ $goal->progress() }}%"></div></div>
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
    </div>
</x-app-layout>
