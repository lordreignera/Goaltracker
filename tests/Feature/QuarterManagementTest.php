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
}
