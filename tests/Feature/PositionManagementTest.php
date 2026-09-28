<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PositionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_position_under_a_section(): void
    {
        $department = Department::create(['name' => 'Finance']);
        $section = $department->sections()->create(['name' => 'Accounting']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('positions.store'), [
            'department_id' => $department->id,
            'section_id' => $section->id,
            'title' => 'Senior Accountant',
            'code' => 'FIN-SA',
            'description' => 'Leads accounting controls.',
        ])->assertRedirect();

        $position = Position::where('title', 'Senior Accountant')->first();

        $this->assertNotNull($position);
        $this->assertSame($department->id, $position->department_id);
        $this->assertSame($section->id, $position->section_id);

        $this->actingAs($admin)
            ->get(route('positions.index'))
            ->assertOk()
            ->assertSee('Senior Accountant')
            ->assertDontSee('Units');
    }

    public function test_staff_cannot_manage_positions(): void
    {
        Role::findOrCreate('Staff');
        $staff = User::factory()->create();
        $staff->assignRole('Staff');

        $this->actingAs($staff)->get(route('positions.index'))->assertForbidden();
    }
}
