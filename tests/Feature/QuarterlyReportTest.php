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
        $response->assertSee('Procurement delay remains');
        $response->assertSee('Procurement follow-up');
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

    public function test_user_can_download_quarterly_report_csv_table(): void
    {
        [$user, $quarter] = $this->reportingData();

        $response = $this->actingAs($user)->get(route('reports.quarterly.csv', [
            'quarter_id' => $quarter->id,
        ]));

        $response->assertOk();
        $response->assertDownload('q1-2026-daily-report-table.csv');

        $content = $response->streamedContent();

        $this->assertStringContainsString('Achievement', $content);
        $this->assertStringContainsString('Procurement delay remains', $content);
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
            'specific_output' => 'Replace outdated machines and confirm they are functional.',
            'weight' => 100,
            'planned_weeks' => 2,
            'starts_at' => '2026-01-01',
            'due_at' => '2026-01-14',
        ]);

        $update = $objective->weeklyUpdates()->create([
            'user_id' => $user->id,
            'report_date' => '2026-01-01',
            'achievement_percentage' => 45,
            'achievement_summary' => 'Installed computers',
            'challenges' => 'Procurement delay remains.',
            'action_points' => 'Procurement follow-up.',
            'status' => 'approved',
        ]);
        $update->reviews()->create([
            'supervisor_id' => $user->id,
            'decision' => 'approved',
            'verified_percentage' => 40,
            'comments' => 'Verified installed computers.',
        ]);

        return [$user, $quarter];
    }
}
