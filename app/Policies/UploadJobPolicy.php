<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\UploadJob;
use App\Models\User;

class UploadJobPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isMerchant() || $user->isAdmin();
    }

    public function view(User $user, UploadJob $uploadJob): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->merchant?->id === $uploadJob->merchant_id;
    }

    public function create(User $user): bool
    {
        return $user->isMerchant() && $user->merchant !== null;
    }

    public function update(User $user, UploadJob $uploadJob): bool
    {
        return $this->view($user, $uploadJob) && $user->isMerchant();
    }

    public function delete(User $user, UploadJob $uploadJob): bool
    {
        return $this->view($user, $uploadJob) && $user->isMerchant();
    }
}
