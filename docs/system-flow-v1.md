# SMART Goals Tracker - System Flow, Structure, and Rules

Version: 1  
Baseline commit: `779cff8`  
Date documented: 2026-07-17  
Application: Laravel SMART Goals Tracker for Africa Renewal Ministries

## 1. Purpose

The SMART Goals Tracker helps the organization plan quarterly goals, break them into weighted sub-goals, collect staff reports, support supervisor review, and calculate progress from supervisor-verified achievement scores.

The system is designed around these principles:

- Main goals describe the broad result.
- Sub-goals/objectives define the measurable deliverables.
- Reports capture activity, evidence, challenges, and action points.
- Not every report changes the percentage.
- Official progress comes only from supervisor-approved progress updates.
- Visibility follows the user's organization scope: department, section, unit, or assigned user.

## 2. Main Application Areas

### Authentication and Registration

Users enter through the login/register flow.

Key behavior:

- New staff can submit account requests through registration.
- Pending users cannot access the system until approved.
- Expired login submissions return to the login page with a useful message instead of a blank page.
- Approved active users can log in.
- Profile and logout actions are accessed from the top navigation user dropdown.

Key files:

- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`
- `app/Actions/Fortify/CreateNewUser.php`
- `app/Http/Responses/PendingApprovalRegisterResponse.php`
- `tests/Feature/AuthenticationTest.php`
- `tests/Feature/RegistrationTest.php`

### Dashboard

The dashboard is the main landing page after login.

It shows:

- Total goals.
- On Track.
- At Risk.
- Off Track.
- Organization score.
- Goal progress by department.
- Tasks/review activity.
- Help and user-guide access.

Design direction:

- Modern SaaS performance-management layout.
- Fixed/collapsible sidebar.
- Sticky top navigation.
- Mobile responsive layout.
- Company name and branding from company settings.

Key files:

- `app/Http/Controllers/DashboardController.php`
- `resources/views/dashboard/index.blade.php`
- `resources/views/layouts/app.blade.php`

## 3. Organization Structure

The organization hierarchy is:

1. Department
2. Section
3. Unit
4. Position
5. User

Users may belong to:

- One primary department.
- Optional section.
- Optional unit.
- Optional position.
- Optional supervisor.
- Additional accessible departments through `department_user`.

Seeded departments include:

- Secretariat
- SEC-Finance
- Childcare
- Community Outreach
- Equip
- Healthy Church & Missions

Key database tables:

- `departments`
- `sections`
- `units`
- `positions`
- `department_user`
- `users`

Key files:

- `database/migrations/2026_06_09_000001_create_organization_tables.php`
- `database/seeders/DatabaseSeeder.php`
- `resources/views/departments/index.blade.php`
- `resources/views/sections/index.blade.php`
- `resources/views/units/index.blade.php`

## 4. Roles and Permissions

The system uses Spatie permissions.

Seeded roles:

- Super Admin
- Admin
- Manager
- Supervisor
- Staff

Seeded permissions:

- `manage departments`
- `manage sections`
- `manage units`
- `manage users`
- `manage quarters`
- `manage goals`
- `review goals`
- `submit daily reports`
- `view reports`
- `export reports`
- `view organization dashboard`

Note: The internal permission name `submit daily reports` remains for compatibility, but the UI displays it as `Submit Reports`.

Role defaults:

- Super Admin: all permissions.
- Admin: all permissions.
- Manager: manage goals, review goals, view/export reports.
- Supervisor: review goals, view reports.
- Staff: submit reports, view reports.

Important user helpers:

- `isAdmin()`
- `isSuperAdmin()`
- `isSupervisor()`
- `canManageGoals()`
- `canReviewGoals()`
- `canManageAdministration()`

Key files:

- `app/Models/User.php`
- `resources/views/roles/management.blade.php`
- `app/Http/Controllers/Admin/RoleManagementController.php`
- `tests/Feature/UserManagementTest.php`

## 5. Goal Creation Flow

Route:

- `GET /goals/create`
- `POST /goals`

Who can create goals:

- Admin and Super Admin.
- Supervisor and Manager.
- Any role with `manage goals`.

Main goal fields:

- Quarter.
- Departments.
- Sections, only for section-level goals.
- Units, only for unit or individual goals.
- Goal level: department, section, unit, individual.
- Main goal title.
- Success measure / metric.
- Deadline.
- Main goal scope.
- Why this is achievable and matters.

Sub-goal/objective fields:

- Objective title.
- Weight percentage.
- Planned duration in weeks.
- Reporting frequency: daily, weekly, or monthly.
- Start date.
- Auto-calculated due date.
- Deliverable / evidence description.

Goal creation rules:

- At least one department is required.
- Section-level goals must select at least one section.
- Unit and individual goals must select at least one unit.
- Unit/individual goals should use units only; section is taken from the selected unit.
- Selected sections/units must belong to selected departments.
- Goal deadline must be inside the selected quarter.
- Objective start and due dates must be inside the selected quarter.
- Objective due date cannot be before objective start date.
- Objective due date must match selected week duration.
- Objective weights must total exactly 100%.

Key files:

- `resources/views/goals/create.blade.php`
- `resources/views/goals/edit.blade.php`
- `app/Http/Requests/Goals/StoreGoalRequest.php`
- `app/Http/Requests/Goals/UpdateGoalRequest.php`
- `app/Services/GoalManagementService.php`
- `tests/Feature/GoalCreationTest.php`

## 6. Goal Assignment Rules

Goals are assigned through `goal_assignments`.

Possible assignment targets:

- Department.
- Section.
- Unit.
- User.

Assignment behavior:

- Department-wide goals store department assignment with no section/unit.
- Section goals store department and section.
- Unit goals store department, section, and unit.
- Unit goals do not store duplicate parent section assignments.
- A goal can be assigned to more than one department/unit where allowed.

Key files:

- `app/Services/GoalManagementService.php`
- `app/Models/GoalAssignment.php`
- `tests/Feature/GoalCreationTest.php`

## 7. Goal Visibility and Access

Goal access is handled by `GoalAccessService`.

Admins:

- Can see all goals.
- Can update all goals.
- Can review all goals.
- Can submit reports for visible goals.

Non-admin users:

- Can see goals assigned to their department access scope.
- Can see goals assigned to their section/unit scope.
- Can see goals assigned directly to them.
- Cannot use `owner_id` alone to bypass department/unit visibility.

Report submission:

- User must be able to view the goal.
- User must be admin, goal manager, or have `submit daily reports`.

Review:

- User must be admin or have supervisor/review capability.
- Non-admin reviewer must belong to the relevant department/unit scope.

Key files:

- `app/Services/GoalAccessService.php`
- `tests/Feature/GoalAccessServiceTest.php`
- `tests/Feature/StaffAccessControlTest.php`

## 8. Goal Detail and Reporting Flow

Route:

- `GET /goals/{goal}`

The goal detail page shows:

- Goal title and assignment scope.
- Success measure / metric.
- Deadline.
- Main goal scope.
- Why achievable and matters.
- Current progress.
- Edit Goal & Objectives button where allowed.
- Submit Goal button where allowed.
- Each objective/sub-goal.
- Report submission form for each objective.
- Existing report table.
- Supervisor review form where allowed.

Key file:

- `resources/views/goals/show.blade.php`

## 9. Reporting Frequency Rules

Each sub-goal has one reporting frequency:

- Daily
- Weekly
- Monthly

The system converts the selected report date into a reporting period.

Daily:

- Period start = report date.
- Period end = report date.
- One report per day for that sub-goal and user.

Weekly:

- Period starts from the objective start date.
- Period lasts up to 7 days.
- One report per week-long reporting period for that sub-goal and user.

Monthly:

- Period start = month start, bounded by objective timeline.
- Period end = month end, bounded by objective timeline.
- One report per month-long reporting period for that sub-goal and user.

Database rule:

- Unique index on `goal_objective_id`, `user_id`, and `report_period_start`.

Key files:

- `app/Models/GoalObjective.php`
- `app/Models/WeeklyUpdate.php`
- `database/migrations/2026_06_09_000002_create_goal_tracking_tables.php`
- `tests/Feature/WeeklyUpdateFlowTest.php`

## 10. Report Submission Rules

Route:

- `POST /objectives/{objective}/weekly-updates`

Report fields:

- Report date.
- Is progress update: yes/no.
- Achievement percentage, only when progress update is checked.
- Achievement summary.
- Challenges.
- Action points / next step.
- Optional evidence file.

Evidence upload:

- Optional.
- Supported: PDF, Word, Excel, JPG, JPEG, PNG.
- Maximum size: 10MB.
- Stored on the public disk under `weekly-update-evidence`.
- Downloaded through a protected route.

Important rule:

- A normal report does not store achievement percentage.
- A progress-update report requires achievement percentage.
- Normal reports can document work without changing official progress.

Routes:

- `POST /objectives/{objective}/weekly-updates`
- `PUT /weekly-updates/{weeklyUpdate}`
- `GET /weekly-updates/{weeklyUpdate}/evidence`

Key files:

- `app/Http/Requests/Goals/StoreWeeklyUpdateRequest.php`
- `app/Http/Requests/Goals/UpdateWeeklyUpdateRequest.php`
- `app/Http/Controllers/Goals/WeeklyUpdateController.php`
- `app/Models/WeeklyUpdate.php`
- `tests/Feature/WeeklyUpdateFlowTest.php`

## 11. Supervisor Review Rules

Route:

- `POST /weekly-updates/{weeklyUpdate}/reviews`

Supervisor decisions:

- Approve.
- Reject.
- Request revision.

Normal report review:

- Supervisor can approve without verified percentage.
- Approval confirms the narrative/evidence.
- It does not change official progress.

Progress-update review:

- Approval requires a verified percentage.
- Verified percentage becomes the official source of truth.
- The user's claimed percentage is not the official score.

Rejected report:

- Report status becomes `rejected`.
- Objective status becomes `rejected`.

Revision requested:

- Report status becomes `revision_requested`.
- Objective status becomes `revision_requested`.

Approved progress update:

- Report status becomes `approved`.
- Objective status becomes `approved` if verified score is below 100.
- Objective status becomes `completed` if verified score is 100.

Key files:

- `app/Http/Controllers/Goals/SupervisorReviewController.php`
- `app/Models/SupervisorReview.php`
- `tests/Feature/WeeklyUpdateFlowTest.php`

## 12. Progress Calculation Rules

The system does not calculate progress by counting reports.

Official sub-goal progress:

- Uses the latest approved supervisor-verified score.
- Ignores unapproved reports.
- Ignores normal reports that are not progress updates.
- Ignores staff claimed score if supervisor verified score differs.

Sub-goal contribution:

```text
Sub-goal contribution = sub-goal weight x supervisor verified score / 100
```

Main goal progress:

```text
Main goal progress = sum of all sub-goal contributions
```

Example:

| Sub-goal | Weight | Supervisor verified score | Contribution |
| --- | ---: | ---: | ---: |
| Train leaders | 30% | 60% | 18% |
| Launch cells | 30% | 80% | 24% |
| Confirm effectiveness | 40% | 50% | 20% |
| Main goal total | 100% | | 62% |

Key files:

- `app/Models/Goal.php`
- `app/Models/GoalObjective.php`
- `app/Models/WeeklyUpdate.php`
- `tests/Feature/ProgressCalculationTest.php`

## 13. Dashboard and Organization Score Rules

Dashboard summary cards:

- Total Goals.
- On Track.
- At Risk.
- Off Track.

Organization score:

- Department score = average progress of goals in that department.
- Organization score = average of department scores.
- Visibility is based on the current user's accessible goal scope.

Example:

| Department | Goal scores | Department score |
| --- | --- | ---: |
| Childcare | 62%, 78% | 70% |
| Community Outreach | 50%, 64% | 57% |
| Finance | 80%, 90% | 85% |

```text
Organization score = (70 + 57 + 85) / 3 = 70.7%
```

Key files:

- `app/Http/Controllers/DashboardController.php`
- `resources/views/dashboard/index.blade.php`
- `tests/Feature/ProgressCalculationTest.php`

## 14. Quarterly Reports and Exports

Routes:

- `GET /reports/quarterly`
- `GET /reports/quarterly/pdf`
- `GET /reports/quarterly/csv`

Quarterly report sections:

- Report header.
- Summary cards.
- Department performance.
- Goal progress flow.
- Reporting table.

Reporting table columns:

- Goal.
- Sub-goal.
- Timeline.
- Frequency.
- Reporting period.
- Report date.
- Progress update.
- Staff claim.
- Supervisor verified.
- Achievement.
- Challenges.
- Action point.
- Evidence.
- Staff.
- Status.
- Supervisor feedback.

PDF:

- Landscape A4.
- Intended for printable review.

CSV:

- Spreadsheet-ready table export.
- Intended for Excel-style review and analysis.

Key files:

- `app/Http/Controllers/Reports/QuarterlyReportController.php`
- `app/Services/QuarterlyReportService.php`
- `resources/views/reports/quarterly.blade.php`
- `resources/views/reports/quarterly-pdf.blade.php`
- `resources/views/reports/partials/quarterly-content.blade.php`
- `tests/Feature/QuarterlyReportTest.php`

## 15. User Guide

Routes:

- `GET /help/user-guide`
- `GET /help/user-guide/pdf`

Purpose:

- Explain how the system works.
- Explain goal creation.
- Explain reporting frequency and progress scores.
- Explain supervisor review.
- Explain main goal progress calculation.
- Explain organization score calculation.

Key files:

- `app/Http/Controllers/HelpController.php`
- `resources/views/help/user-guide.blade.php`
- `resources/views/help/user-guide-pdf.blade.php`
- `tests/Feature/UserGuideTest.php`

## 16. Administration

Administration areas:

- Departments.
- Sections.
- Units.
- Users.
- Quarters / cycles.
- Roles and permissions.
- Company settings.

Access:

- Admin and Super Admin by default.
- Users with specific manage permissions may access relevant areas.

Company settings affect:

- Sidebar company name.
- Login screen company name.
- Reports branding.

Key files:

- `resources/views/layouts/app.blade.php`
- `resources/views/settings/company.blade.php`
- `app/Http/Controllers/Admin/CompanySettingController.php`
- `tests/Feature/CompanySettingsTest.php`

## 17. UI Structure

Main app layout:

- Fixed left sidebar.
- Collapsible sidebar.
- Sticky top navigation.
- Mobile sidebar drawer.
- User dropdown for profile/account/logout.
- Search field.
- Notification icon.
- Light/dark mode button.

Sidebar sections:

- Main: Dashboard.
- Goals: Create Goal, My Goals, Reports.
- Organization: Departments, Sections, Units, Users.
- Administration: Quarters / Cycles, Roles & Permissions, Company Settings.
- Help card: Need Help? View guides and support.

Key file:

- `resources/views/layouts/app.blade.php`

## 18. Database Tables in V1

Core tables:

- `users`
- `departments`
- `sections`
- `units`
- `positions`
- `department_user`
- `quarters`
- `goals`
- `goal_assignments`
- `goal_objectives`
- `weekly_updates`
- `supervisor_reviews`
- `quarterly_reflections`
- `company_settings`
- Spatie permission tables.

Important tracking relationships:

- Goal belongs to quarter.
- Goal has many assignments.
- Goal has many objectives.
- Objective belongs to goal.
- Objective has many reports.
- Report belongs to objective and user.
- Report has many supervisor reviews.
- Supervisor review belongs to report and supervisor.

## 19. Important Validation Rules

Goal validation:

- Quarter must exist.
- Departments required.
- Goal level must be valid.
- Main fields required.
- Objectives required.
- Objective weights must total 100.
- Objective dates must be within quarter.
- Objective due date must match selected planned weeks.

Report validation:

- Report date required.
- Report date must be within sub-goal timeline.
- Report must be unique per user, sub-goal, and reporting period.
- Achievement summary required.
- Achievement percentage required only for progress updates.
- Evidence file must be a supported file type and max 10MB.

Review validation:

- Decision required.
- Approved progress update requires verified percentage.
- Verified percentage must be between 0 and 100.
- Normal report approval does not require verified percentage.

## 20. Test Coverage in V1

Major test areas:

- Authentication.
- Registration.
- Staff access control.
- Goal access service.
- Goal creation and editing.
- Weekly/daily/monthly reporting flow.
- Evidence upload/download.
- Supervisor review.
- Progress calculation.
- Quarterly report view/PDF/CSV.
- User guide.
- User management.
- Company settings.

Current baseline expectation:

- Full suite passes with skipped tests for disabled optional features.
- Last verified suite before this document: `69 passed, 7 skipped`.

## 21. Known Naming Notes

Some internal names remain from the earlier weekly/daily implementation:

- Controller/model route names use `weekly_updates`.
- Permission key is `submit daily reports`.

User-facing labels have been updated to the broader term `reports` or `progress updates` where possible. Avoid renaming internal keys casually unless a migration and permissions transition are planned.

## 22. Version 1 Change Control Notes

Use this document as the reference baseline for future version changes.

When creating Version 2 or later, document:

- What flow changed.
- What database fields changed.
- What permissions changed.
- What calculation rules changed.
- Whether old data needs migration.
- Whether user guide and PDF were updated.
- Which tests prove the new behavior.

