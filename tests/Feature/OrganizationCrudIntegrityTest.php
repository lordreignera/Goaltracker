<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Position;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationCrudIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_complete_department_section_and_position_crud(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('departments.store'), [
            'name' => 'Programs',
        ])->assertRedirect();
        $department = Department::where('name', 'Programs')->firstOrFail();

        $this->actingAs($admin)->put(route('departments.update', $department), [
            'name' => 'Programs and Partnerships',
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('sections.store'), [
            'department_id' => $department->id,
            'name' => 'Field Operations',
        ])->assertRedirect();
        $section = Section::where('name', 'Field Operations')->firstOrFail();

        $this->actingAs($admin)->put(route('sections.update', $section), [
            'department_id' => $department->id,
            'name' => 'Program Operations',
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('positions.store'), [
            'department_id' => $department->id,
            'section_id' => $section->id,
            'title' => 'Program Officer',
        ])->assertRedirect();
        $position = Position::where('title', 'Program Officer')->firstOrFail();

        $this->actingAs($admin)->put(route('positions.update', $position), [
            'department_id' => $department->id,
            'section_id' => $section->id,
            'title' => 'Senior Program Officer',
        ])->assertRedirect();

        $this->actingAs($admin)->delete(route('positions.destroy', $position))->assertRedirect();
        $this->actingAs($admin)->delete(route('sections.destroy', $section))->assertRedirect();
        $this->actingAs($admin)->delete(route('departments.destroy', $department))->assertRedirect();

        $this->assertDatabaseCount('positions', 0);
        $this->assertDatabaseCount('sections', 0);
        $this->assertDatabaseCount('departments', 0);
    }

    public function test_duplicate_names_and_parent_deletion_with_children_are_rejected(): void
    {
        $admin = $this->admin();
        $department = Department::create(['name' => 'Finance']);

        $this->actingAs($admin)->post(route('departments.store'), [
            'name' => 'Finance',
            'form_context' => 'createDepartmentModal',
        ])->assertSessionHasErrors('name')
            ->assertSessionHasInput('form_context', 'createDepartmentModal');

        $section = Section::create([
            'department_id' => $department->id,
            'name' => 'Accounts',
        ]);

        $this->actingAs($admin)->post(route('sections.store'), [
            'department_id' => $department->id,
            'name' => 'Accounts',
        ])->assertSessionHasErrors('name');

        $this->actingAs($admin)
            ->delete(route('departments.destroy', $department))
            ->assertSessionHasErrors('department');

        $this->actingAs($admin)
            ->delete(route('sections.destroy', $section))
            ->assertRedirect();

        Position::create([
            'department_id' => $department->id,
            'title' => 'Finance Manager',
        ]);

        $this->actingAs($admin)
            ->delete(route('departments.destroy', $department))
            ->assertSessionHasErrors('department');

        $this->assertDatabaseHas('departments', ['id' => $department->id]);
        $this->assertDatabaseHas('positions', ['department_id' => $department->id]);
    }

    public function test_staff_cannot_use_organization_administration_endpoints(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $this->actingAs($staff)->get(route('departments.index'))->assertForbidden();
        $this->actingAs($staff)->post(route('sections.store'), [])->assertForbidden();
        $this->actingAs($staff)->post(route('positions.store'), [])->assertForbidden();
        $this->actingAs($staff)->post(route('quarters.store'), [])->assertForbidden();
        $this->actingAs($staff)->post(route('goal-pillars.store'), [])->assertForbidden();
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
    }
}
