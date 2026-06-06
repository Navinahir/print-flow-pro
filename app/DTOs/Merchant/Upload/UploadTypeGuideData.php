<?php

declare(strict_types=1);

namespace App\DTOs\Merchant\Upload;

final readonly class UploadTypeGuideData
{
    /**
     * @param  list<string>  $instructions
     * @param  list<string>  $rejections
     * @param  list<UploadSampleFileData>  $samples
     */
    public function __construct(
        public string $type,
        public array $instructions,
        public array $rejections,
        public array $samples,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'instructions' => $this->instructions,
            'rejections' => $this->rejections,
            'samples' => array_map(
                static fn (UploadSampleFileData $sample): array => $sample->toArray(),
                $this->samples,
            ),
        ];
    }
}
