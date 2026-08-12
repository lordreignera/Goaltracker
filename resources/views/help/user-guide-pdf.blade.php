<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SMART Goals User Guide</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; line-height: 1.45; }
        h1 { font-size: 22px; margin: 0 0 6px; color: #172554; }
        h2 { font-size: 15px; margin: 18px 0 8px; color: #172554; }
        h3 { font-size: 12px; margin: 12px 0 6px; }
        p { margin: 0 0 8px; }
        ul { margin: 0 0 8px 18px; padding: 0; }
        li { margin-bottom: 4px; }
        .muted { color: #64748b; }
        .card { border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; margin-bottom: 10px; }
        .formula { background: #eff6ff; border: 1px solid #bfdbfe; padding: 8px; border-radius: 6px; margin: 8px 0; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0 10px; }
        th, td { border: 1px solid #e2e8f0; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; color: #172554; }
        .total td { background: #eff6ff; font-weight: bold; color: #172554; }
    </style>
</head>
<body>
    <h1>SMART Goals Tracker User Guide</h1>
    <p class="muted">Generated for {{ $generatedBy?->name }} on {{ now()->format('M d, Y') }}</p>

    <div class="card">
        <h2>How the System Works</h2>
        <p>The system helps teams create quarterly goals, break them into weighted sub-goals, submit progress reports, and calculate official progress from supervisor-verified scores.</p>
    </div>

    <h2>Create a Goal</h2>
    <ul>
        <li>Open Create Goal and choose the quarter, department, and goal level.</li>
        <li>Select the pillar, quarter, organization scope, strategic goal, key activities, and deliverables.</li>
        <li>Add sub-goals with deliverable / evidence, weight, start date, and timeline.</li>
        <li>All sub-goal weights must total 100%.</li>
    </ul>

    <h2>Submit a Report</h2>
    <ul>
        <li>Open the goal and find the relevant sub-goal.</li>
        <li>Enter the report date. The system regulates whether the sub-goal reports daily, weekly, or monthly.</li>
        <li>Write what was achieved in Achievement.</li>
        <li>Add blockers in Challenges and the next required response in Action Point / Next Step.</li>
        <li>Attach evidence when available, such as a PDF, Word document, Excel sheet, or photo.</li>
        <li>Tick This report updates progress only when the report should carry an achievement percentage.</li>
        <li>The report affects official progress only when it is a progress update and the supervisor approves a verified score.</li>
    </ul>

    <h2>Reporting Frequency and Progress Scores</h2>
    <p>Not every report should change the percentage. A report can simply record what happened, the challenge faced, the next action, and supporting evidence. The percentage is only required when the user ticks This report updates progress.</p>
    <table>
        <thead>
            <tr><th>Sub-goal cadence</th><th>What the system allows</th><th>Good use case</th></tr>
        </thead>
        <tbody>
            <tr><td><strong>Daily</strong></td><td>One report per day for that sub-goal.</td><td>Daily outreach, attendance tracking, or field implementation.</td></tr>
            <tr><td><strong>Weekly</strong></td><td>One report per week-long reporting period.</td><td>Trainings, departmental rollouts, or recurring team execution.</td></tr>
            <tr><td><strong>Monthly</strong></td><td>One report per month-long reporting period.</td><td>Finance reconciliations, audits, or monthly performance reviews.</td></tr>
        </tbody>
    </table>
    <div class="formula">
        Tick This report updates progress when the report gives a new official achievement percentage for the sub-goal.<br>
        Leave it unticked when the report is only an activity note, challenge, action point, or evidence upload.
    </div>
    <p><strong>Example:</strong> A weekly sub-goal is "Train 20 cell leaders." On Monday, the staff can upload attendance evidence and note that 8 leaders attended without updating progress. On Friday, after the week's work is complete, the staff ticks This report updates progress and claims 40%. The supervisor may verify 35%. The system uses 35% as the official progress score.</p>

    <h2>Supervisor Review</h2>
    <ul>
        <li>Approve normal report confirms the narrative/evidence but does not change the goal percentage.</li>
        <li>Approve progress update requires a verified percentage. This becomes the official source of truth.</li>
        <li>Request revision sends the report back for correction.</li>
        <li>Reject marks the report as not accepted.</li>
    </ul>

    <h2>How Main Goal Progress Is Calculated</h2>
    <div class="formula">
        Sub-goal contribution = sub-goal weight x supervisor verified score.<br>
        Main goal progress = total of all sub-goal contributions.
    </div>
    <h3>Live Example</h3>
    <p>Main goal: Increase the number of effective community cells during the quarter.</p>
    <table>
        <thead>
            <tr><th>Sub-goal</th><th>Weight</th><th>Verified score</th><th>Contribution</th></tr>
        </thead>
        <tbody>
            <tr><td>Train cell leaders</td><td>30%</td><td>60%</td><td>18%</td></tr>
            <tr><td>Launch new community cells</td><td>30%</td><td>80%</td><td>24%</td></tr>
            <tr><td>Follow up and confirm cell effectiveness</td><td>40%</td><td>50%</td><td>20%</td></tr>
            <tr class="total"><td>Main goal progress</td><td>100%</td><td></td><td>62%</td></tr>
        </tbody>
    </table>

    <h2>How Organization Score Is Calculated</h2>
    <div class="formula">
        Department score = average progress of goals in that department.<br>
        Organization score = average of department scores.
    </div>
    <h3>Live Example</h3>
    <table>
        <thead>
            <tr><th>Department</th><th>Goal scores</th><th>Department score</th><th>Org contribution</th></tr>
        </thead>
        <tbody>
            <tr><td>Childcare</td><td>62%, 78%</td><td>70%</td><td>70%</td></tr>
            <tr><td>Community Outreach</td><td>50%, 64%</td><td>57%</td><td>57%</td></tr>
            <tr><td>Finance</td><td>80%, 90%</td><td>85%</td><td>85%</td></tr>
            <tr class="total"><td>Organization score</td><td></td><td></td><td>70.7%</td></tr>
        </tbody>
    </table>
</body>
</html>
