<?php

declare(strict_types=1);

namespace App\Services\Merchant;

use App\DTOs\Merchant\Upload\UploadSampleFileData;
use App\DTOs\Merchant\Upload\UploadTypeGuideData;
use App\Enums\UploadJobType;

class UploadTypeGuideService
{
    /**
     * @return array<string, UploadTypeGuideData>
     */
    public function guidesForForm(): array
    {
        $guides = [];

        foreach (UploadJobType::cases() as $type) {
            $guides[$type->value] = $this->guideFor($type);
        }

        return $guides;
    }

    public function guideFor(UploadJobType $type): UploadTypeGuideData
    {
        $key = 'merchant.uploads.guides.'.$type->value;
        $locale = app()->getLocale();
        $isChinese = str_starts_with($locale, 'zh');

        return new UploadTypeGuideData(
            type: $type->value,
            instructions: $this->translationLines("{$key}.instructions"),
            rejections: $this->translationLines("{$key}.rejections"),
            samples: $this->samplesFor($type, $isChinese),
        );
    }

    /**
     * @return list<UploadSampleFileData>
     */
    private function samplesFor(UploadJobType $type, bool $isChinese): array
    {
        $suffix = $isChinese ? 'zh-TW' : 'en';

        return match ($type) {
            UploadJobType::ThermalLabel => [
                $this->sample(
                    label: (string) __('merchant.uploads.guides.thermal_label.samples.single'),
                    assetPath: 'samples/thermal-labels/sample-single.pdf',
                    downloadName: "thermal-label-sample-single-{$suffix}.pdf",
                    description: (string) __('merchant.uploads.guides.thermal_label.samples.single_hint'),
                ),
                $this->sample(
                    label: (string) __('merchant.uploads.guides.thermal_label.samples.multipage'),
                    assetPath: 'samples/thermal-labels/sample-multipage.pdf',
                    downloadName: "thermal-label-sample-multipage-{$suffix}.pdf",
                    description: (string) __('merchant.uploads.guides.thermal_label.samples.multipage_hint'),
                ),
            ],
            UploadJobType::OrderPdf => [
                $this->sample(
                    label: (string) __('merchant.uploads.guides.order_pdf.samples.a'),
                    assetPath: 'samples/order-pdf/sample-a.pdf',
                    downloadName: "order-pdf-sample-a-{$suffix}.pdf",
                ),
                $this->sample(
                    label: (string) __('merchant.uploads.guides.order_pdf.samples.b'),
                    assetPath: 'samples/order-pdf/sample-b.pdf',
                    downloadName: "order-pdf-sample-b-{$suffix}.pdf",
                ),
            ],
            UploadJobType::PickingList => [
                $this->sample(
                    label: (string) __('merchant.uploads.guides.picking_list.samples.a'),
                    assetPath: 'samples/picking-list/sample-a.xlsx',
                    downloadName: "picking-list-sample-a-{$suffix}.xlsx",
                ),
                $this->sample(
                    label: (string) __('merchant.uploads.guides.picking_list.samples.b'),
                    assetPath: 'samples/picking-list/sample-b.xlsx',
                    downloadName: "picking-list-sample-b-{$suffix}.xlsx",
                ),
            ],
            UploadJobType::DeliveryLabel => [
                $this->sample(
                    label: (string) __('merchant.uploads.guides.delivery_label.samples.csv'),
                    assetPath: $isChinese
                        ? 'samples/delivery-labels/sample-zh-TW.csv'
                        : 'samples/delivery-labels/sample-en.csv',
                    downloadName: $isChinese
                        ? 'delivery-labels-sample-zh-TW.csv'
                        : 'delivery-labels-sample-en.csv',
                ),
                $this->sample(
                    label: (string) __('merchant.uploads.guides.delivery_label.samples.xlsx'),
                    assetPath: 'samples/delivery-labels/sample-address.xlsx',
                    downloadName: "delivery-labels-sample-address-{$suffix}.xlsx",
                ),
            ],
        };
    }

    private function sample(
        string $label,
        string $assetPath,
        string $downloadName,
        ?string $description = null,
    ): UploadSampleFileData {
        return new UploadSampleFileData(
            label: $label,
            assetPath: $assetPath,
            downloadName: $downloadName,
            description: $description,
            previewKind: UploadSampleFileData::previewKindFromPath($assetPath),
        );
    }

    /**
     * @return list<string>
     */
    private function translationLines(string $key): array
    {
        $lines = __($key);

        if (! is_array($lines)) {
            return $lines !== '' ? [(string) $lines] : [];
        }

        return array_values(array_map(static fn ($line): string => (string) $line, $lines));
    }
}
