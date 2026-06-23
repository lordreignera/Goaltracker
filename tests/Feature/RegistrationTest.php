<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_registration_screen_cannot_be_rendered_if_support_is_disabled(): void
    {
        if (Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is enabled.');
        }

        $response = $this->get('/register');

        $response->assertStatus(404);
    }

    public function test_new_users_can_submit_staff_account_requests(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $department = Department::create(['name' => 'ICT Department']);
        $section = $department->sections()->create(['name' => 'Software Development']);
        $position = $section->positions()->create([
            'department_id' => $department->id,
            'title' => 'Software Developer',
        ]);

        $response = $this->post('/register', [
            'first_name' => 'Test',
            'second_name' => 'User',
            'phone_number' => '+256700000000',
            'email' => 'test@example.com',
            'department_id' => $department->id,
            'section_id' => $section->id,
            'position_id' => $position->id,
            'requested_role' => 'Supervisor',
            'password' => 'StrongPass1!',
            'password_confirmation' => 'StrongPass1!',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));

        $user = User::where('email', 'test@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('pending', $user->approval_status);
        $this->assertSame('Supervisor', $user->requested_role);
        $this->assertSame('Test User', $user->name);
        $this->assertSame($position->id, $user->position_id);
        $this->assertTrue($user->accessibleDepartments()->whereKey($department->id)->exists());
    }

    public function test_duplicate_email_and_phone_number_cannot_register(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $department = Department::create(['name' => 'ICT Department']);
        $section = $department->sections()->create(['name' => 'Software Development']);
        $position = $section->positions()->create([
            'department_id' => $department->id,
            'title' => 'Software Developer',
        ]);

        User::factory()->create([
            'email' => 'taken@example.com',
            'phone_number' => '+256700000000',
        ]);

        $response = $this->post('/register', [
            'first_name' => 'Test',
            'second_name' => 'User',
            'phone_number' => '+256700000000',
            'email' => 'taken@example.com',
            'department_id' => $department->id,
            'section_id' => $section->id,
            'position_id' => $position->id,
            'requested_role' => 'Staff',
            'password' => 'StrongPass1!',
            'password_confirmation' => 'StrongPass1!',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $response->assertSessionHasErrors(['email', 'phone_number']);
    }
}
