<?php

declare(strict_types=1);

namespace App\Http\Requests\Merchant\Printing;

use App\Support\MerchantConfig;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreDeliveryLabelCsvRequest extends FormRequest
{
    private const DEFAULT_MAX_FILE_SIZE_KB = 20480;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxKb = (int) (MerchantConfig::get('upload.max_file_size_kb') ?? self::DEFAULT_MAX_FILE_SIZE_KB);

        return [
            'file' => ['required', 'file', File::types(['csv', 'txt'])->max($maxKb)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => __('merchant.delivery_labels.csv.validation.file_required'),
            'file.mimes' => __('merchant.delivery_labels.csv.validation.file_type'),
            'file.max' => __('merchant.delivery_labels.csv.validation.file_too_large'),
        ];
    }
}
