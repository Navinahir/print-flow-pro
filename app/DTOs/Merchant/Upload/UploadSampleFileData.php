<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Upload;

final readonly class UploadSampleFileData
{
    public function __construct(
        public string $label,
        public string $assetPath,
        public string $downloadName,
        public ?string $description = null,
        public string $previewKind = 'none',
    ) {}

    public function isPreviewable(): bool
    {
        return $this->previewKind !== 'none';
    }

    public function previewUrl(): string
    {
        return asset($this->assetPath);
    }

    /**
     * @return array<string, string|null|bool>
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'url' => $this->previewUrl(),
            'download_name' => $this->downloadName,
            'description' => $this->description,
            'preview_kind' => $this->previewKind,
            'previewable' => $this->isPreviewable(),
        ];
    }

    public static function previewKindFromPath(string $assetPath): string
    {
        $extension = strtolower(pathinfo($assetPath, PATHINFO_EXTENSION));

        return match ($extension) {
            'pdf' => 'pdf',
            'csv' => 'csv',
            'xlsx', 'xls' => 'spreadsheet',
            default => 'none',
        };
    }
}
