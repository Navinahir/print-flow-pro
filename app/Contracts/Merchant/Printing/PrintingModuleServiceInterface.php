<?php

declare(strict_types=1);

namespace App\Contracts\Merchant\Printing;

use App\DTOs\Merchant\Printing\PrintingWorkspaceViewData;
use App\Enums\PrintingModule;
use App\Models\User;

interface PrintingModuleServiceInterface
{
    public function module(): PrintingModule;

    public function buildWorkspace(User $user): PrintingWorkspaceViewData;
}
