<?php

declare(strict_types=1);

namespace App\Filament\Resources\MerchantResource\Pages;

use App\Filament\Resources\MerchantResource;
use App\Models\User;
use App\Services\AuditLogService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EditMerchant extends EditRecord
{
    protected static string $resource = MerchantResource::class;

    protected ?string $pendingEmail = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['email'] = $this->record->user?->email;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pendingEmail = isset($data['email']) ? strtolower($data['email']) : null;

        $userId = $data['user_id'] ?? $this->record->user_id;

        if (! $userId) {
            throw ValidationException::withMessages([
                'user_id' => 'Link a user account before setting an email address.',
            ]);
        }

        validator(
            [
                'email' => $this->pendingEmail,
                'user_id' => $userId,
            ],
            [
                'email' => [
                    'required',
                    'string',
                    'lowercase',
                    'email',
                    'max:255',
                    Rule::unique(User::class, 'email')->ignore($userId),
                ],
                'user_id' => ['required', 'exists:users,id'],
            ],
            [
                'email.required' => 'Email is required.',
                'email.email' => 'Please enter a valid email address.',
                'email.unique' => 'This email is already registered to another account.',
            ],
        )->validate();

        unset($data['email']);

        return $data;
    }

    protected function afterSave(): void
    {
        $user = $this->record->fresh(['user'])->user;

        if ($user !== null && $this->pendingEmail !== null && $user->email !== $this->pendingEmail) {
            $user->forceFill([
                'email' => $this->pendingEmail,
                'email_verified_at' => null,
            ])->save();
        }

        app(AuditLogService::class)->logAdmin(
            event: 'merchant.updated',
            description: "Merchant {$this->record->name} updated.",
            auditable: $this->record,
        );
    }
}
