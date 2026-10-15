<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_mfa_enforcement_redirects_unconfirmed_users_to_account_settings(): void
    {
        config(['security.require_mfa' => true]);
        $user = User::factory()->create([
            'approval_status' => 'approved',
            'is_active' => true,
            'two_factor_confirmed_at' => null,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('warning', 'Multi-factor authentication is required. Set it up in Account Settings before continuing.');

        $this->actingAs($user)->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Two Factor Authentication');
    }

    public function test_confirmed_mfa_users_can_access_protected_business_routes(): void
    {
        config(['security.require_mfa' => true]);
        $user = User::factory()->create([
            'approval_status' => 'approved',
            'is_active' => true,
            'two_factor_confirmed_at' => now(),
        ]);

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }

    public function test_mfa_enforcement_returns_a_machine_readable_locked_response(): void
    {
        config(['security.require_mfa' => true]);
        $user = User::factory()->create([
            'approval_status' => 'approved',
            'is_active' => true,
            'two_factor_confirmed_at' => null,
        ]);

        $this->actingAs($user)->getJson(route('dashboard'))
            ->assertStatus(423)
            ->assertJsonStructure(['message', 'setup_url']);
    }

    public function test_security_headers_are_added_to_web_responses(): void
    {
        config(['security.hsts' => true]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(str_replace('http://', 'https://', route('dashboard')));

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }
}
