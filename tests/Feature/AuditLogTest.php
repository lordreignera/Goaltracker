<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_changes_are_recorded_with_actor_and_request_context(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('departments.store'), [
            'name' => 'Monitoring and Evaluation',
            'code' => 'MEL',
        ])->assertRedirect();

        $department = Department::where('name', 'Monitoring and Evaluation')->firstOrFail();
        $log = AuditLog::where('action', 'department.created')
            ->where('subject_type', Department::class)
            ->where('subject_id', $department->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertSame($admin->id, $log->actor_id);
        $this->assertSame('administration', $log->category);
        $this->assertSame('Monitoring and Evaluation', $log->new_values['name']);
        $this->assertNotNull($log->request_id);
        $this->assertNotNull($log->ip_address);
    }

    public function test_sensitive_values_are_redacted_from_user_change_logs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $this->actingAs($admin);
        $user->update(['password' => 'NewSecurePassword!123']);

        $log = AuditLog::where('action', 'user.updated')
            ->where('subject_id', $user->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('[REDACTED]', $log->old_values['password']);
        $this->assertSame('[REDACTED]', $log->new_values['password']);
        $this->assertStringNotContainsString('NewSecurePassword', json_encode($log->toArray()));
    }

    public function test_authentication_events_are_recorded_as_security_events(): void
    {
        $user = User::factory()->create();

        event(new Login('web', $user, false));

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $user->id,
            'category' => 'security',
            'action' => 'authentication.login',
            'subject_type' => User::class,
            'subject_id' => $user->id,
        ]);
    }

    public function test_audit_log_view_is_restricted_and_filterable(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $admin = User::factory()->create(['role' => 'admin']);
        AuditLog::create([
            'category' => 'security',
            'action' => 'authentication.failed',
            'description' => 'Authentication failed.',
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($staff)->get(route('audit-logs.index'))->assertForbidden();

        $this->actingAs($admin)->get(route('audit-logs.index', ['category' => 'security']))
            ->assertOk()
            ->assertSee('Activity and Security History')
            ->assertSee('Authentication Failed')
            ->assertSee('127.0.0.1');
    }

    public function test_audit_logs_cannot_be_updated_or_deleted_through_the_model(): void
    {
        $log = AuditLog::create([
            'category' => 'security',
            'action' => 'test.event',
            'description' => 'Original event.',
        ]);

        try {
            $log->update(['description' => 'Changed event.']);
            $this->fail('Updating an audit log should throw an exception.');
        } catch (LogicException $exception) {
            $this->assertSame('Audit logs are append-only and cannot be updated.', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $log->delete();
    }
}
