from html import escape
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile

OUT = Path("docs/ARM_SMART_Goals_System_Structure_and_Code_Flow.docx")


def p(text="", style=None, align=None):
    ppr = []
    if style:
        ppr.append(f'<w:pStyle w:val="{style}"/>')
    if align:
        ppr.append(f'<w:jc w:val="{align}"/>')
    ppr_xml = f"<w:pPr>{''.join(ppr)}</w:pPr>" if ppr else ""
    return f"<w:p>{ppr_xml}<w:r><w:t xml:space=\"preserve\">{escape(text)}</w:t></w:r></w:p>"


def code(text):
    return (
        "<w:p><w:pPr><w:spacing w:after=\"120\"/></w:pPr>"
        "<w:r><w:rPr><w:rFonts w:ascii=\"Consolas\" w:hAnsi=\"Consolas\"/>"
        "<w:sz w:val=\"18\"/><w:color w:val=\"555555\"/></w:rPr>"
        f"<w:t xml:space=\"preserve\">{escape(text)}</w:t></w:r></w:p>"
    )


def bullet(text):
    return (
        "<w:p><w:pPr><w:numPr><w:ilvl w:val=\"0\"/><w:numId w:val=\"1\"/></w:numPr>"
        "<w:spacing w:after=\"80\"/><w:ind w:left=\"540\" w:hanging=\"270\"/></w:pPr>"
        f"<w:r><w:t>{escape(text)}</w:t></w:r></w:p>"
    )


def numbered(text):
    return (
        "<w:p><w:pPr><w:numPr><w:ilvl w:val=\"0\"/><w:numId w:val=\"2\"/></w:numPr>"
        "<w:spacing w:after=\"80\"/><w:ind w:left=\"540\" w:hanging=\"270\"/></w:pPr>"
        f"<w:r><w:t>{escape(text)}</w:t></w:r></w:p>"
    )


def cell(text, header=False):
    fill = '<w:shd w:fill="E8EEF5"/>' if header else ""
    bold1 = "<w:b/>" if header else ""
    color = '<w:color w:val="1F4D78"/>' if header else ""
    return (
        "<w:tc><w:tcPr><w:tcW w:w=\"3000\" w:type=\"dxa\"/>"
        "<w:tcMar><w:top w:w=\"90\" w:type=\"dxa\"/><w:start w:w=\"120\" w:type=\"dxa\"/>"
        "<w:bottom w:w=\"90\" w:type=\"dxa\"/><w:end w:w=\"120\" w:type=\"dxa\"/></w:tcMar>"
        f"{fill}</w:tcPr><w:p><w:r><w:rPr>{bold1}{color}</w:rPr>"
        f"<w:t xml:space=\"preserve\">{escape(text)}</w:t></w:r></w:p></w:tc>"
    )


def table(headers, rows):
    borders = (
        '<w:tblBorders><w:top w:val="single" w:sz="4" w:color="D9E2EC"/>'
        '<w:left w:val="single" w:sz="4" w:color="D9E2EC"/>'
        '<w:bottom w:val="single" w:sz="4" w:color="D9E2EC"/>'
        '<w:right w:val="single" w:sz="4" w:color="D9E2EC"/>'
        '<w:insideH w:val="single" w:sz="4" w:color="D9E2EC"/>'
        '<w:insideV w:val="single" w:sz="4" w:color="D9E2EC"/></w:tblBorders>'
    )
    xml = [f'<w:tbl><w:tblPr><w:tblW w:w="9360" w:type="dxa"/>{borders}</w:tblPr>']
    xml.append("<w:tr>" + "".join(cell(h, True) for h in headers) + "</w:tr>")
    for row in rows:
        xml.append("<w:tr>" + "".join(cell(str(v)) for v in row) + "</w:tr>")
    xml.append("</w:tbl>")
    xml.append(p())
    return "".join(xml)


body = []
body.append(p("90-Day SMART Goals Accountability Tracker", "Title", "center"))
body.append(p("System Structure, MVC Alignment, and Code Flow", "Subtitle", "center"))
body.append(p("Africa Renewal Ministries | Laravel 12, Jetstream, Spatie Permission, MySQL", None, "center"))
body.append(p("This document explains how the application is organized and how the Laravel code layers talk to each other. It is a maintenance guide for future development, debugging, onboarding, and handover."))

body.append(p("1. System Purpose", "Heading1"))
body.append(p("The application helps Africa Renewal Ministries plan, track, review, and report on 90-day SMART goals. It connects staff work to unit, department, and organization performance through weighted objectives, weekly updates, and supervisor review."))
for item in [
    "Admin and Super Admin users manage setup data, users, roles, quarters, and organization-wide visibility.",
    "Supervisors and managers create and review goals within their department or unit scope.",
    "Staff submit weekly updates against objectives visible to their department or unit.",
    "Goal progress is calculated from completed objectives and their weights.",
]:
    body.append(bullet(item))

body.append(p("2. Technology Stack", "Heading1"))
body.append(table(["Layer", "Technology", "Main Responsibility"], [
    ["Backend", "Laravel 12 / PHP 8.2+", "Routes, controllers, validation, services, models, transactions."],
    ["Database", "MySQL", "Persistent data for users, organization structure, goals, updates, reviews, sessions, cache, permissions."],
    ["Auth", "Laravel Jetstream + Fortify", "Login, registration, profile, password, two-factor, approval-aware authentication."],
    ["Permissions", "Spatie Laravel Permission", "Role and permission storage, checks, and assignment UI."],
    ["Frontend", "Blade, Livewire-ready Jetstream, Bootstrap/Tailwind assets", "Dashboard, forms, tables, admin pages, login, registration."],
    ["Build/Test", "Vite/NPM and PHPUnit", "Frontend build and feature regression checks."],
]))

body.append(p("3. High-Level Laravel Request Flow", "Heading1"))
for step in [
    "Browser opens a route from routes/web.php.",
    "The auth middleware verifies that the user is logged in.",
    "The route calls a controller such as DashboardController, GoalController, or UserManagementController.",
    "Authorization happens in the controller, Form Request, or GoalAccessService.",
    "Validation happens in Form Request classes or simple controller validation.",
    "Business rules run in services when the logic is shared or complex.",
    "Eloquent models read or write MySQL and expose relationships.",
    "The controller returns a Blade view with the data needed for the page.",
    "The Blade view renders the final UI, forms, tables, filters, and action buttons.",
]:
    body.append(numbered(step))

body.append(p("4. Folder Map", "Heading1"))
body.append(table(["Path", "Role in the App"], [
    ["routes/web.php", "Authenticated web routes and URL-to-controller mapping."],
    ["app/Http/Controllers", "Request coordination, authorization checks, validation calls, model/service usage, view responses."],
    ["app/Http/Requests", "Reusable validation and authorization for larger forms."],
    ["app/Services", "Business rules that should not live directly inside controllers."],
    ["app/Models", "Eloquent models and database relationships."],
    ["resources/views", "Blade templates for auth, dashboard, admin CRUD, goals, and profile pages."],
    ["database/migrations", "Database schema creation and change order."],
    ["database/seeders", "Default departments, units, roles, permissions, and Super Admin account."],
    ["tests/Feature", "Regression tests for auth, access control, goals, quarters, and user management."],
]))

body.append(p("5. Routes and Page Ownership", "Heading1"))
body.append(table(["URL / Route Area", "Controller", "View", "Purpose"], [
    ["/", "Route redirect", "auth/login.blade.php", "First page is the login screen."],
    ["/dashboard", "DashboardController", "dashboard/index.blade.php", "Role-filtered performance summary and visible goals."],
    ["/departments", "DepartmentController", "departments/index.blade.php", "Admin CRUD for departments."],
    ["/units", "UnitController", "units/index.blade.php", "Admin CRUD for units."],
    ["/quarters", "QuarterController", "quarters/index.blade.php", "Admin creation/listing of 90-day quarters."],
    ["/users/management", "UserManagementController", "users/management.blade.php", "User approvals, edits, status, activity, delete."],
    ["/roles/management", "RoleManagementController", "roles/management.blade.php", "Role creation and permission assignment."],
    ["/goals", "GoalController", "goals/index.blade.php", "Filtered and paginated goal list."],
    ["/goals/create", "GoalController", "goals/create.blade.php", "Create a main goal with weighted objectives."],
    ["/goals/{goal}/edit", "GoalController", "goals/edit.blade.php", "Edit assignments and objectives while preserving 100% weight."],
    ["/goals/{goal}", "GoalController", "goals/show.blade.php", "Goal details, objectives, updates, and reviews."],
]))

body.append(p("6. Data Model and Relationships", "Heading1"))
body.append(p("Organization structure:"))
body.append(code("Organization -> Department -> Unit -> Staff"))
body.append(p("Goal tracking structure:"))
body.append(code("Quarter -> Goal -> GoalObjective -> WeeklyUpdate -> SupervisorReview"))
body.append(table(["Model", "Table", "Key Relationships"], [
    ["User", "users", "belongsTo Department, Unit, Supervisor; hasMany Staff; belongsTo Approver; has Spatie roles."],
    ["Department", "departments", "hasMany Units, Users, Goals."],
    ["Unit", "units", "belongsTo Department; hasMany Users and Goals."],
    ["Quarter", "quarters", "hasMany Goals; stores starts_at, ends_at, is_active."],
    ["Goal", "goals", "belongsTo Quarter, Department, Unit, Owner; hasMany Objectives; belongsToMany assignedDepartments and assignedUnits."],
    ["GoalObjective", "goal_objectives", "belongsTo Goal; hasMany WeeklyUpdates; stores weight and status."],
    ["WeeklyUpdate", "weekly_updates", "belongsTo Objective and User; hasMany SupervisorReviews."],
    ["SupervisorReview", "supervisor_reviews", "belongsTo WeeklyUpdate and Supervisor."],
    ["QuarterlyReflection", "quarterly_reflections", "belongsTo User and Quarter; stores wins, challenges, lessons, next focus."],
]))

body.append(p("7. Migration Order", "Heading1"))
body.append(table(["Migration", "Why It Runs There"], [
    ["0001_01_01_000000_create_users_table", "Creates users before organization foreign keys are attached."],
    ["0001_01_01_000001_create_cache_table", "Creates database cache tables."],
    ["0001_01_01_000002_create_jobs_table", "Creates queue/job tables."],
    ["2026_06_09_000001_create_organization_tables", "Creates departments and units, then adds organization fields to users."],
    ["2026_06_09_000002_create_goal_tracking_tables", "Creates quarters, goals, objectives, weekly updates, reviews, and reflections."],
    ["2026_06_09_114626_create_permission_tables", "Creates Spatie permission tables."],
    ["2026_06_09_120000+ user field migrations", "Adds registration, requested role, approval, active, and activity fields."],
    ["2026_06_10_000001_create_goal_assignment_tables", "Adds goal_department and goal_unit pivots for multi-department/unit goal assignment."],
]))

body.append(p("8. Authentication and Registration Flow", "Heading1"))
for item in [
    "CreateNewUser validates first name, second name, phone number, email, department, unit, requested role, and password.",
    "New users are created as pending account requests until a Super Admin approves them.",
    "FortifyServiceProvider loads departments and units into the registration page.",
    "FortifyServiceProvider blocks pending, rejected, or inactive users during login and shows account-status messages.",
    "last_login_at is updated after successful login.",
]:
    body.append(bullet(item))

body.append(p("9. Roles, Permissions, and User Approval", "Heading1"))
body.append(table(["Role", "Expected Access"], [
    ["Super Admin", "Everything: users, roles, departments, units, quarters, goals, reviews, reports, all departments and units."],
    ["Admin", "Administration areas, with Super Admin-only restrictions where needed."],
    ["Manager / Supervisor", "Goals and reviews within assigned department/unit visibility."],
    ["Staff", "Visible goals/objectives in their department/unit and weekly update submission."],
]))
for item in [
    "UserManagementController owns user listing, filtering, edit, approval, rejection, and deletion.",
    "RoleManagementController owns role list, role creation, and permission assignment.",
    "UpdateUserRequest validates user edits without changing passwords.",
    "DatabaseSeeder creates departments, units, permissions, roles, and the Super Admin account.",
]:
    body.append(bullet(item))

body.append(p("10. Goal Access Control", "Heading1"))
body.append(p("GoalAccessService is the central gate for goal visibility, update, review, and query scoping. Controllers and model scopes should use it instead of repeating department/unit checks."))
body.append(table(["Method", "Meaning"], [
    ["canViewGoal(User, Goal)", "Super Admin can view all. Owners can view their goals. Others must match department/unit assignment."],
    ["canUpdateGoal(User, Goal)", "User must view the goal and be Admin, Supervisor/Manager, or owner."],
    ["canReviewGoal(User, Goal)", "Super Admin can review all. Supervisors/Managers review within their department/unit scope."],
    ["scopeVisibleGoals(query, User)", "Applies visibility filtering to dashboard, goal list, reviews, and reports."],
]))
for item in [
    "If assigned units exist, a non-admin user must belong to one of those units.",
    "If no assigned units exist, users in the assigned department can see the goal.",
    "Legacy department_id and unit_id are still maintained for compatibility.",
]:
    body.append(bullet(item))

body.append(p("11. Goal Creation and Objective Flow", "Heading1"))
body.append(table(["Layer", "Responsibility"], [
    ["goals/create.blade.php", "Displays the form for main goal, departments, units, quarter, level, and objectives."],
    ["StoreGoalRequest", "Authorizes Admin/Supervisor users and validates form fields."],
    ["GoalController@store", "Receives validated input and delegates persistence."],
    ["GoalManagementService@createGoal", "Checks weights equal 100%, applies access rules, saves goal, syncs assignments, creates objectives in one transaction."],
    ["Goal model", "Stores goal data and exposes relationships to quarter, assignments, owner, and objectives."],
]))
body.append(code("Main Goal Progress = Sum of completed objective weights"))
body.append(p("Example: completed approved objectives weighing 20% and 25% make main goal progress 45%."))

body.append(p("12. Goal Editing Flow", "Heading1"))
body.append(p("A goal with objectives totaling 100% should not receive random extra objectives from the detail page. Objective changes happen together from Edit Goal so the total remains valid."))
for item in [
    "GoalController@edit loads the goal, assigned departments, assigned units, quarter, and objectives.",
    "UpdateGoalRequest validates the payload and verifies objective IDs belong to the current goal.",
    "GoalManagementService@updateGoal recalculates total weight, syncs assignments, deletes removed objectives, updates existing objectives, and creates new objective rows.",
    "ObjectiveController@store redirects users to Edit Goal with a message explaining this rule.",
]:
    body.append(bullet(item))

body.append(p("13. Weekly Update and Review Flow", "Heading1"))
for step in [
    "Staff opens a visible goal and selects an objective.",
    "WeeklyUpdateController checks canViewGoal through GoalAccessService.",
    "The weekly update is saved with week number, summary, achievements, challenges, next actions, and percentage estimate.",
    "SupervisorReviewController checks canReviewGoal before saving a review.",
    "The review decision updates the weekly update status.",
    "If approved, the objective status becomes completed; if rejected or revision requested, the objective status follows that decision.",
    "Goal progress reads completed objective weights through Goal::progress().",
]:
    body.append(numbered(step))

body.append(p("14. Dashboard Flow", "Heading1"))
for item in [
    "DashboardController loads visible goals through Goal::visibleTo($user).",
    "It calculates active goals, completed goals, and average visible progress.",
    "For admins, it calculates organization score from department goal averages.",
    "It counts pending reviews by submitted weekly updates whose objective goal is visible to the user.",
    "It passes the result to dashboard/index.blade.php.",
]:
    body.append(bullet(item))

body.append(p("15. View Layout and UI Alignment", "Heading1"))
body.append(table(["View Area", "Purpose"], [
    ["layouts/guest.blade.php", "Guest layout for login and registration."],
    ["layouts/app.blade.php", "Authenticated layout with maroon sidebar, top bar, profile, and role-aware navigation."],
    ["auth/login.blade.php", "First page, styled login with password visibility and maroon ARM theme."],
    ["auth/register.blade.php", "Staff access request form with department/unit selection and requested role."],
    ["dashboard/index.blade.php", "Performance dashboard filtered by access rules."],
    ["users/management.blade.php", "User approval and account management table."],
    ["roles/management.blade.php", "Role and permission management page."],
    ["goals/*.blade.php", "Goal list, create form, edit form, and detail/review screen."],
]))

body.append(p("16. Maintainability Rules Going Forward", "Heading1"))
for item in [
    "Put complex business rules in services, not directly in Blade or controllers.",
    "Use Form Request classes when a form has authorization plus multi-field validation.",
    "Use GoalAccessService for goal visibility, update, and review checks everywhere.",
    "Use model relationships instead of manual joins when the relationship already exists.",
    "Keep route names stable when changing controllers so views and tests do not break.",
    "Add feature tests when changing access control, registration, approvals, goal weighting, or review flow.",
    "Do not add direct objective creation outside goal edit unless the 100% weight rule is enforced in one transaction.",
]:
    body.append(bullet(item))

body.append(p("17. Testing Map", "Heading1"))
body.append(table(["Test File", "Coverage"], [
    ["AuthenticationTest", "Login screen, valid/invalid login, pending account message."],
    ["RegistrationTest", "Registration page, staff request, duplicate email and phone prevention."],
    ["UserManagementTest", "Edit panel, approved-user rejection guard, role permissions, role creation restrictions."],
    ["StaffAccessControlTest", "Staff navigation restrictions and department/unit goal visibility."],
    ["GoalAccessServiceTest", "Core department/unit goal visibility rules."],
    ["GoalCreationTest", "Create page, objective weights, multi assignment, edit objectives."],
    ["QuarterManagementTest", "Automatic 90-day quarter end-date calculation."],
]))

body.append(p("18. Practical Development Checklist", "Heading1"))
for item in [
    "Run php artisan test.",
    "Run npm run build when Blade, CSS, JS, or frontend assets changed.",
    "Run php artisan migrate when migrations changed.",
    "Verify staff cannot access admin-only pages.",
    "Verify non-admin users only see goals in their assigned department or unit.",
    "Verify objective weights still equal 100% after creating or editing a goal.",
    "Check the UI on mobile when layout, tables, or forms change.",
]:
    body.append(bullet(item))

body.append(p("Appendix A: Core Flow Diagrams", "Heading1"))
body.append(p("A.1 User Registration and Approval", "Heading2"))
body.append(code("register.blade.php -> Fortify -> CreateNewUser -> users.approval_status=pending -> UserManagementController@approve -> assign Spatie role -> login allowed"))
body.append(p("A.2 Main Goal Creation", "Heading2"))
body.append(code("goals/create.blade.php -> StoreGoalRequest -> GoalController@store -> GoalManagementService@createGoal -> Goal + pivot assignments + objectives -> goals/show.blade.php"))
body.append(p("A.3 Objective Completion", "Heading2"))
body.append(code("goals/show.blade.php -> WeeklyUpdateController@store -> SupervisorReviewController@store -> objective.status=completed -> Goal::progress()"))
body.append(p("A.4 Dashboard Metrics", "Heading2"))
body.append(code("DashboardController -> Goal::visibleTo(user) -> GoalAccessService::scopeVisibleGoals -> Goal::progress() -> dashboard/index.blade.php"))
body.append(p("End of system structure guide", None, "center"))

document_xml = f'''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
<w:body>
{''.join(body)}
<w:sectPr><w:pgSz w:w="12240" w:h="15840"/><w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="708" w:footer="708" w:gutter="0"/></w:sectPr>
</w:body>
</w:document>'''

styles_xml = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
<w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/><w:qFormat/><w:pPr><w:spacing w:after="120" w:line="300" w:lineRule="auto"/></w:pPr><w:rPr><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/><w:sz w:val="22"/></w:rPr></w:style>
<w:style w:type="paragraph" w:styleId="Title"><w:name w:val="Title"/><w:basedOn w:val="Normal"/><w:qFormat/><w:pPr><w:spacing w:after="160"/></w:pPr><w:rPr><w:b/><w:color w:val="800C18"/><w:sz w:val="44"/></w:rPr></w:style>
<w:style w:type="paragraph" w:styleId="Subtitle"><w:name w:val="Subtitle"/><w:basedOn w:val="Normal"/><w:qFormat/><w:pPr><w:spacing w:after="160"/></w:pPr><w:rPr><w:color w:val="1F4D78"/><w:sz w:val="28"/></w:rPr></w:style>
<w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:qFormat/><w:pPr><w:keepNext/><w:spacing w:before="360" w:after="200"/></w:pPr><w:rPr><w:b/><w:color w:val="2E74B5"/><w:sz w:val="32"/></w:rPr></w:style>
<w:style w:type="paragraph" w:styleId="Heading2"><w:name w:val="heading 2"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:qFormat/><w:pPr><w:keepNext/><w:spacing w:before="280" w:after="140"/></w:pPr><w:rPr><w:b/><w:color w:val="2E74B5"/><w:sz w:val="26"/></w:rPr></w:style>
<w:style w:type="paragraph" w:styleId="Bullet"><w:name w:val="Bullet"/><w:basedOn w:val="Normal"/><w:pPr><w:ind w:left="540" w:hanging="270"/><w:spacing w:after="80"/></w:pPr><w:rPr><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/></w:rPr></w:style>
<w:style w:type="paragraph" w:styleId="Numbered"><w:name w:val="Numbered"/><w:basedOn w:val="Normal"/><w:pPr><w:ind w:left="540" w:hanging="270"/><w:spacing w:after="80"/></w:pPr></w:style>
</w:styles>'''

content_types = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
<Override PartName="/word/numbering.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.numbering+xml"/>
</Types>'''

rels = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>'''

doc_rels = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/numbering" Target="numbering.xml"/>
</Relationships>'''

numbering_xml = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:numbering xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
<w:abstractNum w:abstractNumId="1"><w:lvl w:ilvl="0"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val="•"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="num" w:pos="540"/></w:tabs><w:ind w:left="540" w:hanging="270"/></w:pPr></w:lvl></w:abstractNum>
<w:num w:numId="1"><w:abstractNumId w:val="1"/></w:num>
<w:abstractNum w:abstractNumId="2"><w:lvl w:ilvl="0"><w:start w:val="1"/><w:numFmt w:val="decimal"/><w:lvlText w:val="%1."/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="num" w:pos="540"/></w:tabs><w:ind w:left="540" w:hanging="270"/></w:pPr></w:lvl></w:abstractNum>
<w:num w:numId="2"><w:abstractNumId w:val="2"/></w:num>
</w:numbering>'''

with ZipFile(OUT, "w", ZIP_DEFLATED) as z:
    z.writestr("[Content_Types].xml", content_types)
    z.writestr("_rels/.rels", rels)
    z.writestr("word/_rels/document.xml.rels", doc_rels)
    z.writestr("word/document.xml", document_xml)
    z.writestr("word/styles.xml", styles_xml)
    z.writestr("word/numbering.xml", numbering_xml)

print(OUT)
