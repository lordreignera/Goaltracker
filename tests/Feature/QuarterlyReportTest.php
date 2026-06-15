<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Goal;
use App\Models\Quarter;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuarterlyReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_quarterly_report_for_visible_goals(): void
    {
        [$user, $quarter] = $this->reportingData();

        $response = $this->actingAs($user)->get(route('reports.quarterly.index', [
            'quarter_id' => $quarter->id,
        ]));

        $response->assertOk();
        $response->assertSee('Quarterly Performance Report');
        $response->assertSee('Improve ICT Service Delivery');
        $response->assertSee('Installed computers');
    }

    public function test_user_can_download_quarterly_report_pdf(): void
    {
        [$user, $quarter] = $this->reportingData();

        $response = $this->actingAs($user)->get(route('reports.quarterly.pdf', [
            'quarter_id' => $quarter->id,
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    private function reportingData(): array
    {
        $department = Department::create(['name' => 'ICT Department']);
        $unit = Unit::create(['department_id' => $department->id, 'name' => 'Software Development Unit']);
        $quarter = Quarter::create([
            'name' => 'Q1 2026',
            'starts_at' => '2026-01-01',
            'ends_at' => '2026-03-31',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'department_id' => $department->id,
            'unit_id' => $unit->id,
            'role' => 'staff',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $goal = Goal::create([
            'quarter_id' => $quarter->id,
            'owner_id' => $user->id,
            'title' => 'Improve ICT Service Delivery',
            'level' => 'unit',
        ]);
        $goal->assignments()->create(['department_id' => $department->id, 'unit_id' => $unit->id]);

        $objective = $goal->objectives()->create([
            'title' => 'Upgrade staff computers',
            'specific_output' => 'Replace outdated machines.',
            'success_measure' => 'All assigned computers are functional and signed off.',
            'weight' => 100,
            'planned_weeks' => 2,
            'starts_at' => '2026-01-01',
            'due_at' => '2026-01-14',
        ]);

        $objective->weeklyUpdates()->create([
            'user_id' => $user->id,
            'week_number' => 1,
            'week_starting' => '2026-01-01',
            'progress_summary' => 'Completed Kampala office setup.',
            'achievements' => 'Installed computers',
            'challenges' => 'Procurement delay',
            'next_actions' => 'Finish staff onboarding',
            'status' => 'approved',
        ]);

        return [$user, $quarter];
    }
}
