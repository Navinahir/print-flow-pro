<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PrintJobStatus;
use App\Models\PrintJob;
use App\Models\User;

class PrintJobPolicy
{
    public function view(User $user, PrintJob $printJob): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->merchant?->id === $printJob->merchant_id;
    }

    public function download(User $user, PrintJob $printJob): bool
    {
        if (! $this->view($user, $printJob)) {
            return false;
        }

        return in_array($printJob->status, [PrintJobStatus::Ready, PrintJobStatus::Downloaded], true);
    }

    public function regenerate(User $user, PrintJob $printJob): bool
    {
        return $this->download($user, $printJob);
    }
}
