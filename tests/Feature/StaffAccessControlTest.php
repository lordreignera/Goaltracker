<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Goal;
use App\Models\Quarter;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_dashboard_hides_administration_navigation(): void
    {
        Role::findOrCreate('Staff');

        $staff = User::factory()->create([
            'role' => 'staff',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        $staff->assignRole('Staff');

        $response = $this->actingAs($staff)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('Goals');
        $response->assertDontSee('User Management');
        $response->assertDontSee('Departments');
        $response->assertDontSee('Units');
        $response->assertDontSee('Roles &amp; Permissions', false);
        $response->assertDontSee('Quarters');
    }

    public function test_staff_cannot_open_administration_routes_directly(): void
    {
        Role::findOrCreate('Staff');

        $staff = User::factory()->create([
            'role' => 'staff',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        $staff->assignRole('Staff');

        $this->actingAs($staff)->get(route('users.management.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('departments.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('units.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('roles.management.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('quarters.index'))->assertForbidden();
    }

    public function test_staff_only_sees_goals_for_their_department_or_unit(): void
    {
        Role::findOrCreate('Staff');

        $department = Department::create(['name' => 'ICT Department']);
        $otherDepartment = Department::create(['name' => 'Finance Department']);
        $unit = Unit::create(['department_id' => $department->id, 'name' => 'Software Development Unit']);
        $otherUnit = Unit::create(['department_id' => $department->id, 'name' => 'Infrastructure Unit']);
        $quarter = Quarter::create(['name' => 'Q1 2026', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);

        $staff = User::factory()->create([
            'department_id' => $department->id,
            'unit_id' => $unit->id,
            'role' => 'staff',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        $staff->assignRole('Staff');

        $departmentGoal = Goal::create([
            'quarter_id' => $quarter->id,
            'title' => 'Department Goal',
        ]);
        $departmentGoal->assignments()->create(['department_id' => $department->id]);

        $ownUnitGoal = Goal::create([
            'quarter_id' => $quarter->id,
            'title' => 'Own Unit Goal',
        ]);
        $ownUnitGoal->assignments()->create(['department_id' => $department->id, 'unit_id' => $unit->id]);

        $otherUnitGoal = Goal::create([
            'quarter_id' => $quarter->id,
            'title' => 'Other Unit Goal',
        ]);
        $otherUnitGoal->assignments()->create(['department_id' => $department->id, 'unit_id' => $otherUnit->id]);

        $otherDepartmentGoal = Goal::create([
            'quarter_id' => $quarter->id,
            'title' => 'Other Department Goal',
        ]);
        $otherDepartmentGoal->assignments()->create(['department_id' => $otherDepartment->id]);

        $response = $this->actingAs($staff)->get(route('goals.index'));

        $response->assertOk();
        $response->assertSee('Department Goal');
        $response->assertSee('Own Unit Goal');
        $response->assertDontSee('Other Unit Goal');
        $response->assertDontSee('Other Department Goal');
        $response->assertDontSee('Create Goal Set');
    }
}
