<?php

declare(strict_types=1);

namespace App\Http\Requests\Merchant\Printing;

use Illuminate\Foundation\Http\FormRequest;

class ValidateAspectRatioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'width' => ['required_without:file', 'integer', 'min:1', 'max:20000'],
            'height' => ['required_without:file', 'integer', 'min:1', 'max:20000'],
            'file' => ['required_without:width,height', 'file', 'max:20480', 'mimes:jpg,jpeg,png,gif,webp,bmp'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'width.required_without' => __('merchant.preview.aspect_ratio.validation.width_required'),
            'height.required_without' => __('merchant.preview.aspect_ratio.validation.height_required'),
            'file.required_without' => __('merchant.preview.aspect_ratio.validation.file_or_dimensions_required'),
            'file.mimes' => __('merchant.preview.aspect_ratio.validation.unsupported_file'),
        ];
    }
}
