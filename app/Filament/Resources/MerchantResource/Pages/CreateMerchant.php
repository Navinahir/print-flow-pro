<?php

declare(strict_types=1);

namespace App\Filament\Resources\MerchantResource\Pages;

use App\Enums\Role as RoleEnum;
use App\Filament\Resources\MerchantResource;
use App\Models\Merchant;
use App\Models\User;
use App\Services\AuditLogService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMerchant extends CreateRecord
{
    protected static string $resource = MerchantResource::class;

    /**
     * @var array{email: string, password: string}
     */
    protected array $pendingUserAccount = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->pendingUserAccount = [
            'email' => $data['email'],
            'password' => $data['password'],
        ];

        unset($data['email'], $data['password'], $data['password_confirmation']);

        $data['created_by'] = auth()->id();

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $this->pendingUserAccount['email'],
            'password' => $this->pendingUserAccount['password'],
            'role' => RoleEnum::Merchant,
            'email_verified_at' => now(),
        ]);

        $user->syncRoles([RoleEnum::Merchant->value]);

        $data['user_id'] = $user->id;

        return Merchant::query()->create($data);
    }

    protected function afterCreate(): void
    {
        app(AuditLogService::class)->logAdmin(
            event: 'merchant.created',
            description: "Merchant {$this->record->name} created.",
            auditable: $this->record,
        );
    }
}
