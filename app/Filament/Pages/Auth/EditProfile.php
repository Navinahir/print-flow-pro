<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EditProfile extends BaseEditProfile
{
    public static function getLabel(): string
    {
        return __('admin.nav.profile');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.profile.information.title'))
                    ->description(__('admin.profile.information.description'))
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                    ])
                    ->footerActions([
                        $this->getSaveProfileAction(),
                    ]),
                Section::make(__('admin.profile.password.title'))
                    ->description(__('admin.profile.password.description'))
                    ->schema([
                        $this->getCurrentPasswordFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->footerActions([
                        $this->getSavePasswordAction(),
                    ]),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form');
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [];
    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('admin.profile.information.name'))
            ->placeholder(__('admin.profile.information.name_placeholder'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('admin.profile.information.email'))
            ->placeholder(__('admin.profile.information.email_placeholder'))
            ->email()
            ->required()
            ->maxLength(255)
            ->readOnly();
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('admin.profile.password.new'))
            ->placeholder(__('admin.profile.password.new_placeholder'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->rule(Password::default())
            ->autocomplete('new-password')
            ->dehydrated(false);
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('admin.profile.password.confirm'))
            ->placeholder(__('admin.profile.password.confirm_placeholder'))
            ->password()
            ->autocomplete('new-password')
            ->revealable(filament()->arePasswordsRevealable())
            ->same('password')
            ->dehydrated(false);
    }

    protected function getCurrentPasswordFormComponent(): Component
    {
        return TextInput::make('currentPassword')
            ->label(__('admin.profile.password.current'))
            ->placeholder(__('admin.profile.password.current_placeholder'))
            ->password()
            ->autocomplete('current-password')
            ->currentPassword(guard: Filament::getAuthGuard())
            ->revealable(filament()->arePasswordsRevealable())
            ->dehydrated(false);
    }

    protected function getSaveProfileAction(): Action
    {
        return Action::make('saveProfile')
            ->label(__('admin.profile.information.save'))
            ->action('saveProfile');
    }

    protected function getSavePasswordAction(): Action
    {
        return Action::make('savePassword')
            ->label(__('admin.profile.password.save'))
            ->action('savePassword');
    }

    public function saveProfile(): void
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return;
        }

        $validated = $this->validate([
            'data.name' => ['required', 'string', 'max:255'],
            'data.email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->getUser()->getKey()),
            ],
        ]);

        /** @var Model $user */
        $user = $this->getUser();
        $user->fill($validated['data']);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Notification::make()
            ->success()
            ->title(__('admin.profile.information.saved'))
            ->send();
    }

    public function savePassword(): void
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return;
        }

        $validated = $this->validate([
            'data.currentPassword' => ['required', 'current_password:'.Filament::getAuthGuard()],
            'data.password' => ['required', Password::default(), 'same:data.passwordConfirmation'],
            'data.passwordConfirmation' => ['required', 'string'],
        ]);

        /** @var Model $user */
        $user = $this->getUser();
        $hashedPassword = Hash::make($validated['data']['password']);
        $user->forceFill(['password' => $hashedPassword])->save();

        if (request()->hasSession()) {
            request()->session()->put([
                'password_hash_'.Filament::getAuthGuard() => $hashedPassword,
            ]);
        }

        $this->data['currentPassword'] = null;
        $this->data['password'] = null;
        $this->data['passwordConfirmation'] = null;

        Notification::make()
            ->success()
            ->title(__('admin.profile.password.saved'))
            ->send();
    }
}
