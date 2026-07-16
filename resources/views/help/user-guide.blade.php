<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">User Guide</h1>
    </x-slot>

    <style>
        .guide-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 320px;
            gap: 18px;
        }

        .guide-card {
            border: 1px solid var(--sg-border);
            border-radius: 14px;
            background: var(--sg-surface);
            box-shadow: var(--sg-shadow);
        }

        .guide-card h2 {
            color: var(--sg-text);
        }

        .guide-list {
            margin: 0;
            padding-left: 1.1rem;
        }

        .guide-list li {
            margin-bottom: .55rem;
        }

        .guide-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: .35rem .65rem;
            background: #eff6ff;
            color: var(--sg-navy);
            border: 1px solid #bfdbfe;
            font-size: .8rem;
            font-weight: 800;
        }

        .guide-step {
            display: grid;
            grid-template-columns: 34px minmax(0, 1fr);
            gap: 12px;
        }

        .guide-step-number {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #dbeafe;
            color: var(--sg-blue);
            font-weight: 800;
        }

        .calculation-box {
            border: 1px solid var(--sg-border);
            border-radius: 12px;
            background: var(--sg-surface);
            overflow: hidden;
        }

        .calculation-row {
            display: grid;
            grid-template-columns: minmax(160px, 1.5fr) repeat(3, minmax(90px, .8fr));
            gap: 12px;
            padding: 12px 16px;
            border-top: 1px solid var(--sg-border);
            align-items: center;
            font-size: .92rem;
        }

        .calculation-row:first-child {
            border-top: 0;
            background: #f8fafc;
            color: var(--sg-text);
            font-weight: 800;
        }

        .calculation-total {
            background: #eff6ff;
            color: var(--sg-navy);
            font-weight: 800;
        }

        .formula-card {
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            background: #eff6ff;
            color: var(--sg-navy);
            padding: 14px 16px;
        }

        .guide-table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border: 1px solid var(--sg-border);
            border-radius: 12px;
        }

        .guide-table th,
        .guide-table td {
            border-bottom: 1px solid var(--sg-border);
            padding: 12px;
            text-align: left;
            vertical-align: top;
        }

        .guide-table th {
            background: #f8fafc;
            color: var(--sg-text);
            font-size: .86rem;
        }

        .guide-table tr:last-child td {
            border-bottom: 0;
        }

        @media (max-width: 991.98px) {
            .guide-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .calculation-row {
                grid-template-columns: 1fr;
                gap: 4px;
            }

            .calculation-row > div:not(:first-child)::before {
                color: var(--sg-muted);
                font-weight: 700;
            }

            .calculation-row > div:nth-child(2)::before {
                content: "Weight: ";
            }

            .calculation-row > div:nth-child(3)::before {
                content: "Verified score: ";
            }

            .calculation-row > div:nth-child(4)::before {
                content: "Contribution: ";
            }

            .calculation-row:first-child {
                display: none;
            }
        }
    </style>

    <div class="guide-grid">
        <div class="d-grid gap-3">
            <section class="guide-card p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                    <span class="guide-pill align-self-start">Start Here</span>
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('help.user-guide.pdf') }}">Download PDF</a>
                </div>
                <h2 class="h5 fw-bold mb-2">How the SMART Goals Tracker Works</h2>
                <p class="text-muted mb-0">
                    The system helps teams create quarterly goals, break them into weighted sub-goals, submit progress reports, and calculate official progress from supervisor-verified scores.
                </p>
            </section>

            <section class="guide-card p-4">
                <h2 class="h5 fw-bold mb-3">Create a Goal</h2>
                <div class="d-grid gap-3">
                    <div class="guide-step">
                        <span class="guide-step-number">1</span>
                        <div>
                            <div class="fw-bold">Open Create Goal</div>
                            <div class="text-muted small">Choose the quarter, department, and goal level.</div>
                        </div>
                    </div>
                    <div class="guide-step">
                        <span class="guide-step-number">2</span>
                        <div>
                            <div class="fw-bold">Enter the main goal details</div>
                            <div class="text-muted small">Add the goal title, scope, success measure / metric, reason it matters, and deadline.</div>
                        </div>
                    </div>
                    <div class="guide-step">
                        <span class="guide-step-number">3</span>
                        <div>
                            <div class="fw-bold">Add sub-goals</div>
                            <div class="text-muted small">Each sub-goal needs a deliverable / evidence, weight, start date, and timeline. All weights must total 100%.</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="guide-card p-4">
                <h2 class="h5 fw-bold mb-3">Submit a Report</h2>
                <ul class="guide-list text-muted">
                    <li>Open the goal and find the relevant sub-goal.</li>
                    <li>Enter the report date. The system regulates whether the sub-goal reports daily, weekly, or monthly.</li>
                    <li>Write what was achieved in the Achievement field.</li>
                    <li>Add any blockers in Challenges and the next required response in Action Point / Next Step.</li>
                    <li>Attach evidence when available, such as a PDF, Word document, Excel sheet, or photo.</li>
                    <li>Tick This report updates progress only when the report should carry an achievement percentage.</li>
                    <li>You can submit one report per sub-goal reporting period.</li>
                    <li>A submitted report affects official progress only when it is a progress update and the supervisor approves a verified score.</li>
                </ul>
            </section>

            <section class="guide-card p-4">
                <h2 class="h5 fw-bold mb-3">Reporting Frequency and Progress Scores</h2>
                <p class="text-muted">
                    Not every report should change the percentage. A report can simply record what happened, the challenge faced, the next action, and supporting evidence. The percentage is only required when the user ticks <strong>This report updates progress</strong>.
                </p>

                <div class="table-responsive mb-3">
                    <table class="guide-table">
                        <thead>
                            <tr>
                                <th>Sub-goal cadence</th>
                                <th>What the system allows</th>
                                <th>Good use case</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Daily</strong></td>
                                <td>One report per day for that sub-goal.</td>
                                <td>Activities that need close follow-up, such as daily outreach, attendance tracking, or field implementation.</td>
                            </tr>
                            <tr>
                                <td><strong>Weekly</strong></td>
                                <td>One report per week-long reporting period.</td>
                                <td>Work that moves meaningfully every week, such as trainings, departmental rollouts, or recurring team execution.</td>
                            </tr>
                            <tr>
                                <td><strong>Monthly</strong></td>
                                <td>One report per month-long reporting period.</td>
                                <td>Work measured by monthly milestones, such as finance reconciliations, audits, or monthly performance reviews.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="formula-card mb-3">
                    <div class="fw-bold">How to decide whether to tick progress update</div>
                    <div class="small mt-1">Tick it when the report gives a new official achievement percentage for the sub-goal.</div>
                    <div class="small">Leave it unticked when the report is only an activity note, challenge, action point, or evidence upload.</div>
                </div>

                <h3 class="h6 fw-bold mb-2">Example</h3>
                <p class="text-muted mb-0">
                    A weekly sub-goal is “Train 20 cell leaders.” On Monday, the staff can upload attendance evidence and note that 8 leaders attended without updating progress. On Friday, after the week’s work is complete, the staff ticks <strong>This report updates progress</strong> and claims 40%. The supervisor reviews the evidence and may verify 35%. The system uses 35% as the official progress score.
                </p>
            </section>

            <section class="guide-card p-4">
                <h2 class="h5 fw-bold mb-3">Supervisor Review</h2>
                <p class="text-muted">
                    Supervisors see the staff claim, review the report history for that sub-goal, and enter a verified percentage only when the report is a progress update.
                </p>
                <ul class="guide-list text-muted">
                    <li><strong>Approve normal report:</strong> confirms the narrative/evidence but does not change the goal percentage.</li>
                    <li><strong>Approve progress update:</strong> requires a verified percentage. This becomes the official source of truth.</li>
                    <li><strong>Request revision:</strong> sends the report back for correction.</li>
                    <li><strong>Reject:</strong> marks the report as not accepted.</li>
                </ul>
            </section>

            <section class="guide-card p-4">
                <h2 class="h5 fw-bold mb-3">How Progress Is Calculated</h2>
                <p class="text-muted">
                    The system does not simply count how many reports were submitted. It calculates official progress from the latest approved supervisor-verified score for each sub-goal.
                </p>

                <div class="formula-card mb-3">
                    <div class="fw-bold">Main goal formula</div>
                    <div class="small mt-1">Sub-goal contribution = sub-goal weight x supervisor verified score</div>
                    <div class="small">Main goal progress = total of all sub-goal contributions</div>
                </div>

                <h3 class="h6 fw-bold mb-2">Live Example: Main Goal With Three Sub-goals</h3>
                <p class="text-muted small">
                    Main goal: Increase the number of effective community cells during the quarter.
                </p>

                <div class="calculation-box mb-3">
                    <div class="calculation-row">
                        <div>Sub-goal</div>
                        <div>Weight</div>
                        <div>Verified score</div>
                        <div>Contribution</div>
                    </div>
                    <div class="calculation-row">
                        <div>Train cell leaders</div>
                        <div>30%</div>
                        <div>60%</div>
                        <div>18%</div>
                    </div>
                    <div class="calculation-row">
                        <div>Launch new community cells</div>
                        <div>30%</div>
                        <div>80%</div>
                        <div>24%</div>
                    </div>
                    <div class="calculation-row">
                        <div>Follow up and confirm cell effectiveness</div>
                        <div>40%</div>
                        <div>50%</div>
                        <div>20%</div>
                    </div>
                    <div class="calculation-row calculation-total">
                        <div>Main goal progress</div>
                        <div>100%</div>
                        <div></div>
                        <div>62%</div>
                    </div>
                </div>

                <p class="text-muted mb-0">
                    In this example, the main goal is 62% complete because 18% + 24% + 20% = 62%. If a staff member claimed 90% but the supervisor verified 60%, the system uses 60%.
                </p>
            </section>

            <section class="guide-card p-4">
                <h2 class="h5 fw-bold mb-3">How Organization Score Is Calculated</h2>
                <p class="text-muted">
                    The organization score is a roll-up of goal progress across the departments visible to the user. Each department score is based on the average progress of that department's goals, and the organization score averages those department scores.
                </p>

                <div class="formula-card mb-3">
                    <div class="fw-bold">Organization score formula</div>
                    <div class="small mt-1">Department score = average progress of goals in that department</div>
                    <div class="small">Organization score = average of department scores</div>
                </div>

                <h3 class="h6 fw-bold mb-2">Live Example: Three Departments</h3>
                <div class="calculation-box mb-3">
                    <div class="calculation-row">
                        <div>Department</div>
                        <div>Goal scores</div>
                        <div>Department score</div>
                        <div>Org contribution</div>
                    </div>
                    <div class="calculation-row">
                        <div>Childcare</div>
                        <div>62%, 78%</div>
                        <div>70%</div>
                        <div>70%</div>
                    </div>
                    <div class="calculation-row">
                        <div>Community Outreach</div>
                        <div>50%, 64%</div>
                        <div>57%</div>
                        <div>57%</div>
                    </div>
                    <div class="calculation-row">
                        <div>Finance</div>
                        <div>80%, 90%</div>
                        <div>85%</div>
                        <div>85%</div>
                    </div>
                    <div class="calculation-row calculation-total">
                        <div>Organization score</div>
                        <div></div>
                        <div></div>
                        <div>70.7%</div>
                    </div>
                </div>

                <p class="text-muted mb-0">
                    In this example, the organization score is 70.7% because (70 + 57 + 85) divided by 3 departments = 70.7%.
                </p>
            </section>
        </div>

        <aside class="d-grid gap-3 align-content-start">
            <section class="guide-card p-4">
                <h2 class="h6 fw-bold mb-3">Quick Links</h2>
                <div class="d-grid gap-2">
                    <a class="btn btn-primary" href="{{ route('goals.create') }}">Create Goal</a>
                    <a class="btn btn-outline-secondary" href="{{ route('goals.index') }}">View Goals</a>
                    <a class="btn btn-outline-secondary" href="{{ route('reports.quarterly.index') }}">Quarterly Report</a>
                    <a class="btn btn-outline-secondary" href="{{ route('reports.quarterly.csv') }}">Download Report Table</a>
                    <a class="btn btn-outline-secondary" href="{{ route('help.user-guide.pdf') }}">Download User Guide</a>
                </div>
            </section>

            <section class="guide-card p-4">
                <h2 class="h6 fw-bold mb-3">Admin Setup Checklist</h2>
                <ul class="guide-list text-muted small">
                    <li>Create departments, sections, and units.</li>
                    <li>Approve staff accounts and assign roles.</li>
                    <li>Confirm the active quarter / cycle.</li>
                    <li>Review roles and permissions when access changes.</li>
                </ul>
            </section>

            <section class="guide-card p-4">
                <h2 class="h6 fw-bold mb-3">Need Support?</h2>
                <p class="text-muted small mb-0">
                    Contact your system administrator if you cannot see a goal, cannot submit a report, or need your department/unit assignment corrected.
                </p>
            </section>
        </aside>
    </div>
</x-app-layout>
