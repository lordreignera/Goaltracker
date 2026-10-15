<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Goal;
use App\Models\Quarter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EvidenceSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_evidence_defaults_to_a_private_disk(): void
    {
        $this->assertSame('private', config('filesystems.evidence_disk'));
    }

    public function test_upload_is_rejected_when_the_configured_scanner_is_unavailable(): void
    {
        config([
            'evidence.scan_enabled' => true,
            'evidence.scanner_binary' => '__missing_clamav_binary__',
        ]);
        Storage::fake('private');
        [$staff, $objective] = $this->objectiveFixture();

        $this->actingAs($staff)->post(route('objectives.weekly-updates.store', $objective), [
            'report_date' => '2026-01-01',
            'achievement_summary' => 'This upload should fail closed.',
            'evidence_file' => UploadedFile::fake()->image('evidence.jpg'),
        ])->assertSessionHasErrors('evidence_file');

        $this->assertDatabaseCount('weekly_updates', 0);
        Storage::disk('private')->assertDirectoryEmpty('weekly-update-evidence');
    }

    public function test_retention_pruning_removes_old_file_but_preserves_report_metadata(): void
    {
        config(['evidence.retention_days' => 30]);
        Storage::fake('private');
        [$staff, $objective] = $this->objectiveFixture();
        Storage::disk('private')->put('weekly-update-evidence/old.pdf', 'old evidence');

        $update = $objective->weeklyUpdates()->create([
            'user_id' => $staff->id,
            'report_date' => '2026-01-01',
            'reporting_frequency' => 'weekly',
            'report_period_start' => '2026-01-01',
            'report_period_end' => '2026-01-07',
            'achievement_summary' => 'Historical report with retained metadata.',
            'evidence_path' => 'weekly-update-evidence/old.pdf',
            'evidence_original_name' => 'old.pdf',
            'status' => 'approved',
        ]);
        $update->forceFill(['created_at' => now()->subDays(31)])->saveQuietly();

        $this->artisan('evidence:prune')->assertSuccessful();

        $update->refresh();
        $this->assertNull($update->evidence_path);
        $this->assertNull($update->evidence_original_name);
        $this->assertNotNull($update->evidence_deleted_at);
        Storage::disk('private')->assertMissing('weekly-update-evidence/old.pdf');
    }

    private function objectiveFixture(): array
    {
        $department = Department::create(['name' => 'Evidence Department']);
        $quarter = Quarter::create(['name' => 'Evidence Q1', 'starts_at' => '2026-01-01', 'ends_at' => '2026-03-31']);
        $staff = User::factory()->create([
            'department_id' => $department->id,
            'role' => 'staff',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
        Permission::findOrCreate('submit daily reports');
        Role::findOrCreate('Staff')->syncPermissions(['submit daily reports']);
        $staff->assignRole('Staff');
        $goal = Goal::create([
            'quarter_id' => $quarter->id,
            'created_by' => $staff->id,
            'owner_id' => $staff->id,
            'status' => 'submitted',
        ]);
        $goal->assignments()->create(['department_id' => $department->id]);
        $objective = $goal->objectives()->create([
            'title' => 'Evidence objective',
            'key_activities' => 'Collect evidence.',
            'specific_output' => 'Evidence package.',
            'weight' => 100,
            'planned_weeks' => 4,
            'reporting_frequency' => ['weekly'],
            'starts_at' => '2026-01-01',
            'due_at' => '2026-03-31',
        ]);

        return [$staff, $objective];
    }
}
