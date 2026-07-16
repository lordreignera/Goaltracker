<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserGuideTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_guide_requires_authentication(): void
    {
        $this->get(route('help.user-guide'))
            ->assertRedirect(route('login'));

        $this->get(route('help.user-guide.pdf'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_open_user_guide_from_help_card(): void
    {
        $user = User::factory()->create([
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('help.user-guide'))
            ->assertSee('Need Help?')
            ->assertSee('View guides and support');

        $this->actingAs($user)->get(route('help.user-guide'))
            ->assertOk()
            ->assertSee('User Guide')
            ->assertSee('How the SMART Goals Tracker Works')
            ->assertSee('Supervisor Review')
            ->assertSee('Reporting Frequency and Progress Scores')
            ->assertSee('This report updates progress')
            ->assertSee('One report per week-long reporting period')
            ->assertSee('Main goal formula')
            ->assertSee('Organization score formula')
            ->assertSee('Increase the number of effective community cells');
    }

    public function test_authenticated_user_can_download_user_guide_pdf(): void
    {
        $user = User::factory()->create([
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $this->actingAs($user)->get(route('help.user-guide.pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
