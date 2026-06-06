<?php

declare(strict_types=1);

namespace App\Exceptions\Merchant\Pdf;

class PdfStorageException extends PdfEngineException
{
    public static function directoryCreationFailed(string $path): self
    {
        return new self(__('merchant.pdf.storage.directory_failed', [
            'path' => $path,
        ]));
    }

    public static function fileMissing(string $path): self
    {
        return new self(__('merchant.pdf.storage.file_missing', [
            'path' => basename($path),
        ]));
    }
}
