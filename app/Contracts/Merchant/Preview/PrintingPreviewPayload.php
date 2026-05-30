<?php

declare(strict_types=1);

namespace App\Contracts\Merchant\Preview;

use App\Enums\PrintingPreviewType;

interface PrintingPreviewPayload
{
    public function type(): PrintingPreviewType;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
