<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\UpdateProfileInformationForm;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_profile_information_is_available(): void
    {
        $this->actingAs($user = User::factory()->create());

        $component = Livewire::test(UpdateProfileInformationForm::class);

        $this->assertEquals($user->name, $component->state['name']);
        $this->assertEquals($user->email, $component->state['email']);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $this->actingAs($user = User::factory()->create());

        Livewire::test(UpdateProfileInformationForm::class)
            ->set('state', ['name' => 'Test Name', 'email' => 'test@example.com'])
            ->call('updateProfileInformation');

        $this->assertEquals('Test Name', $user->fresh()->name);
        $this->assertEquals('test@example.com', $user->fresh()->email);
    }

    public function test_account_settings_page_has_product_title_and_password_visibility_controls(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('profile.show'));

        $response->assertOk()
            ->assertSee('<title>Account Settings | '.config('app.name').'</title>', false)
            ->assertSee('Complete these four checks')
            ->assertSee('MFA optional')
            ->assertSee('data-password-toggle="current_password"', false)
            ->assertSee('data-password-toggle="password"', false)
            ->assertSee('data-password-toggle="password_confirmation"', false)
            ->assertDontSee('<title>Laravel</title>', false);
    }

    public function test_guest_pages_use_the_product_title(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk()
            ->assertSee('<title>'.config('app.name').'</title>', false)
            ->assertDontSee('<title>Laravel</title>', false);
    }

    public function test_dashboard_displays_the_authenticated_users_department_position_and_section(): void
    {
        $department = Department::create(['name' => 'ICT Department']);
        $section = $department->sections()->create(['name' => 'Software Development']);
        $position = Position::create([
            'department_id' => $department->id,
            'section_id' => $section->id,
            'title' => 'Software Developer',
        ]);
        $user = User::factory()->create([
            'department_id' => $department->id,
            'section_id' => $section->id,
            'position_id' => $position->id,
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Department:')
            ->assertSee($department->name)
            ->assertSee('Position:')
            ->assertSee($position->title)
            ->assertSee('Section:')
            ->assertSee($section->name);
    }
}
