<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Events\Dispatcher;

class SecurityEventSubscriber
{
    public function __construct(private AuditLogger $audit) {}

    public function login(Login $event): void
    {
        $this->securityEvent('authentication.login', 'User logged in.', $event->user);
    }

    public function failed(Failed $event): void
    {
        $this->securityEvent(
            'authentication.failed',
            'Authentication failed.',
            $event->user instanceof User ? $event->user : null,
            ['email' => $event->credentials['email'] ?? null]
        );
    }

    public function lockout(Lockout $event): void
    {
        $this->securityEvent('authentication.lockout', 'Authentication was rate limited.', null, [
            'email' => $event->request->input('email'),
        ]);
    }

    public function logout(Logout $event): void
    {
        $this->securityEvent('authentication.logout', 'User logged out.', $event->user);
    }

    public function passwordReset(PasswordReset $event): void
    {
        $this->securityEvent('authentication.password_reset', 'User password was reset.', $event->user);
    }

    public function registered(Registered $event): void
    {
        $this->securityEvent('authentication.registered', 'User registered.', $event->user);
    }

    public function verified(Verified $event): void
    {
        $this->securityEvent('authentication.email_verified', 'User email was verified.', $event->user);
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'login',
            Failed::class => 'failed',
            Lockout::class => 'lockout',
            Logout::class => 'logout',
            PasswordReset::class => 'passwordReset',
            Registered::class => 'registered',
            Verified::class => 'verified',
        ];
    }

    private function securityEvent(string $action, string $description, ?User $user = null, array $metadata = []): void
    {
        $this->audit->record(
            category: 'security',
            action: $action,
            description: $description,
            subject: $user,
            metadata: $metadata,
            actor: $user,
        );
    }
}
