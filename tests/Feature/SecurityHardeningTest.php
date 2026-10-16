<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_mfa_is_optional_for_application_access(): void
    {
        $user = User::factory()->create([
            'approval_status' => 'approved',
            'is_active' => true,
            'two_factor_confirmed_at' => null,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_users_with_mfa_enabled_can_access_protected_business_routes(): void
    {
        $user = User::factory()->create([
            'approval_status' => 'approved',
            'is_active' => true,
            'two_factor_confirmed_at' => now(),
        ]);

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
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
