<?php

declare(strict_types=1);

namespace App\View\Components\Merchant;

use App\Models\User;
use App\Support\UserAvatar as UserAvatarHelper;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UserAvatar extends Component
{
    public string $initials;

    public ?string $photoUrl;

    public function __construct(
        public User $user,
        public string $size = 'md',
    ) {
        $this->initials = UserAvatarHelper::initials($user->name);
        $this->photoUrl = UserAvatarHelper::photoUrl($user);
    }

    public function render(): View
    {
        return view('merchant.components.user-avatar');
    }
}
