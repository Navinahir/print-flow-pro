<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AuditLogChannel;
use App\Models\AuditLog;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;

class AuditLogService
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function log(
        AuditLogChannel $channel,
        string $event,
        ?string $description = null,
        ?Model $auditable = null,
        ?User $user = null,
        ?Merchant $merchant = null,
        array $properties = [],
        ?Request $request = null,
    ): AuditLog {
        $request ??= RequestFacade::instance();

        return AuditLog::query()->create([
            'user_id' => $user?->id ?? auth()->id(),
            'merchant_id' => $merchant?->id,
            'channel' => $channel,
            'event' => $event,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'description' => $description,
            'properties' => $properties === [] ? null : $properties,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    public function logAdmin(
        string $event,
        ?string $description = null,
        ?Model $auditable = null,
        array $properties = [],
    ): AuditLog {
        return $this->log(
            channel: AuditLogChannel::Admin,
            event: $event,
            description: $description,
            auditable: $auditable,
            properties: $properties,
        );
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    public function logUpload(
        string $event,
        ?string $description = null,
        ?Model $auditable = null,
        ?Merchant $merchant = null,
        array $properties = [],
    ): AuditLog {
        return $this->log(
            channel: AuditLogChannel::Upload,
            event: $event,
            description: $description,
            auditable: $auditable,
            merchant: $merchant,
            properties: $properties,
        );
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    public function logAuth(
        string $event,
        ?User $user = null,
        ?string $description = null,
        array $properties = [],
    ): AuditLog {
        return $this->log(
            channel: AuditLogChannel::Auth,
            event: $event,
            description: $description,
            user: $user,
            properties: $properties,
        );
    }
}
