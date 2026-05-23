<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class LogAuthenticationActivity
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function handleLogin(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $this->auditLogService->logAuth(
            event: 'auth.login',
            user: $event->user,
            description: "User {$event->user->email} logged in.",
            properties: ['guard' => $event->guard],
        );
    }

    public function handleLogout(Logout $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $this->auditLogService->logAuth(
            event: 'auth.logout',
            user: $event->user,
            description: "User {$event->user->email} logged out.",
            properties: ['guard' => $event->guard],
        );
    }

    public function handleFailed(Failed $event): void
    {
        $this->auditLogService->logAuth(
            event: 'auth.failed',
            description: 'Failed login attempt.',
            properties: [
                'email' => $event->credentials['email'] ?? null,
                'guard' => $event->guard,
            ],
        );
    }
}
