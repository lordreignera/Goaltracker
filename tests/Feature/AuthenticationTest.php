<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_expired_login_submission_returns_to_login_with_message(): void
    {
        Route::post('/_expired-session-test', function () {
            throw new TokenMismatchException;
        })->middleware('web');

        $response = $this->from('/login')->post('/_expired-session-test');

        $this->assertGuest('web');
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('session_expired');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_pending_users_are_told_to_wait_for_admin_approval(): void
    {
        $user = User::factory()->create([
            'approval_status' => 'pending',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'email' => 'Your account is awaiting approval. Please contact the administrator.',
        ]);
    }

    public function test_inactive_user_with_an_existing_session_is_logged_out(): void
    {
        $user = User::factory()->create([
            'approval_status' => 'approved',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'email' => 'Your account is inactive. Please contact the administrator.',
        ]);
        $this->assertGuest();
    }

    public function test_pending_user_with_an_existing_session_is_logged_out(): void
    {
        $user = User::factory()->create([
            'approval_status' => 'pending',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'email' => 'Your account is awaiting approval. Please contact the administrator.',
        ]);
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_reach_framework_managed_profile_routes(): void
    {
        $user = User::factory()->create([
            'approval_status' => 'approved',
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertRedirect(route('login'));

        $this->assertGuest('web');
    }

    public function test_pending_api_token_cannot_read_authenticated_user_endpoint(): void
    {
        $user = User::factory()->create([
            'approval_status' => 'pending',
            'is_active' => true,
        ]);
        Sanctum::actingAs($user);

        $this->getJson('/api/user')
            ->assertForbidden()
            ->assertJson([
                'message' => 'Your account is awaiting approval. Please contact the administrator.',
            ]);
    }
}
