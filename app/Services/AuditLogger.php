<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditLogger
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'remember_token',
        'current_password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'token',
        'access_token',
        'refresh_token',
        'evidence_path',
    ];

    public function record(
        string $category,
        string $action,
        string $description,
        ?Model $subject = null,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = [],
        ?User $actor = null,
    ): AuditLog {
        $request = $this->request();
        $resolvedActor = $actor ?? Auth::user();
        $actorId = $resolvedActor?->getKey();

        if ($actorId && ! User::query()->whereKey($actorId)->exists()) {
            $metadata['actor_snapshot'] = [
                'id' => $actorId,
                'name' => $resolvedActor->name,
                'email' => $resolvedActor->email,
            ];
            $actorId = null;
        }

        return AuditLog::create([
            'actor_id' => $actorId,
            'category' => $category,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'old_values' => $this->sanitize($oldValues) ?: null,
            'new_values' => $this->sanitize($newValues) ?: null,
            'metadata' => $this->sanitize($metadata + [
                'source' => app()->runningInConsole() ? 'console' : 'web',
            ]),
            'ip_address' => $request?->ip(),
            'user_agent' => Str::limit((string) $request?->userAgent(), 1024, ''),
            'request_id' => $this->requestId($request),
        ]);
    }

    public function recordModelEvent(Model $model, string $event): AuditLog
    {
        $attributes = collect($event === 'updated' ? $model->getChanges() : $model->getAttributes())
            ->except(['created_at', 'updated_at'])
            ->all();
        $oldValues = [];
        $newValues = [];

        if ($event === 'created') {
            $newValues = $attributes;
        } elseif ($event === 'deleted') {
            $oldValues = $attributes;
        } else {
            $newValues = $attributes;
            $oldValues = collect(array_keys($attributes))
                ->mapWithKeys(fn (string $key) => [$key => $model->getRawOriginal($key)])
                ->all();
        }

        $modelName = Str::snake(class_basename($model));

        return $this->record(
            category: $this->categoryFor($model),
            action: "{$modelName}.{$event}",
            description: sprintf('%s #%s was %s.', class_basename($model), $model->getKey(), $event),
            subject: $model,
            oldValues: $oldValues,
            newValues: $newValues,
            metadata: ['changed_fields' => array_keys($attributes)],
        );
    }

    private function categoryFor(Model $model): string
    {
        return match (true) {
            $model instanceof User => 'security',
            str_starts_with($model::class, 'Spatie\\Permission\\Models\\') => 'security',
            in_array(class_basename($model), ['Goal', 'GoalObjective', 'GoalAssignment'], true) => 'goals',
            in_array(class_basename($model), ['WeeklyUpdate', 'SupervisorReview', 'QuarterlyReflection'], true) => 'reporting',
            default => 'administration',
        };
    }

    private function sanitize(array $values): array
    {
        foreach ($values as $key => $value) {
            if (in_array(Str::lower((string) $key), self::SENSITIVE_KEYS, true)) {
                $values[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $values[$key] = $this->sanitize($value);
            }
        }

        return $values;
    }

    private function request(): ?Request
    {
        return app()->bound('request') && request() instanceof Request ? request() : null;
    }

    private function requestId(?Request $request): ?string
    {
        if (! $request) {
            return null;
        }

        if (! $request->attributes->has('audit_request_id')) {
            $request->attributes->set('audit_request_id', (string) Str::uuid());
        }

        return $request->attributes->get('audit_request_id');
    }
}
