<?php

namespace Tests\Feature;

use App\Models\Quarter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuarterManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_quarter_end_date_is_calculated_from_start_date(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('quarters.store'), [
            'name' => 'Q1 2026',
            'starts_at' => '2026-01-01',
            'is_active' => '1',
        ]);

        $response->assertRedirect();

        $quarter = Quarter::where('name', 'Q1 2026')->first();

        $this->assertNotNull($quarter);
        $this->assertSame('2026-03-31', $quarter->ends_at->toDateString());
        $this->assertTrue($quarter->is_active);
    }

    public function test_quarter_names_and_dates_cannot_duplicate_or_overlap(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        Quarter::create([
            'name' => 'Q1 2026',
            'starts_at' => '2026-01-01',
            'ends_at' => '2026-03-31',
        ]);

        $this->actingAs($admin)->post(route('quarters.store'), [
            'name' => 'Q1 2026',
            'starts_at' => '2026-04-01',
            'form_context' => 'createQuarterModal',
        ])->assertSessionHasErrors('name');

        $this->actingAs($admin)->post(route('quarters.store'), [
            'name' => 'Q2 2026',
            'starts_at' => '2026-03-15',
            'form_context' => 'createQuarterModal',
        ])->assertSessionHasErrors('starts_at')
            ->assertSessionHasInput('form_context', 'createQuarterModal');

        $this->assertSame(1, Quarter::count());
    }
}
