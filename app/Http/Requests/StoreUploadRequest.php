<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\UploadJobType;
use App\Support\MerchantConfig;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreUploadRequest extends FormRequest
{
    private const DEFAULT_MAX_FILE_SIZE_KB = 20480;

    private const DEFAULT_MAX_FILES_PER_JOB = 20;

    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\UploadJob::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $type = UploadJobType::tryFrom((string) $this->input('type'));
        $extensions = $type?->fileExtensions() ?? ['pdf', 'csv', 'xlsx', 'xls'];
        $maxKb = (int) (MerchantConfig::get('upload.max_file_size_kb') ?? self::DEFAULT_MAX_FILE_SIZE_KB);
        $maxFiles = (int) (MerchantConfig::get('upload.max_files_per_job') ?? self::DEFAULT_MAX_FILES_PER_JOB);

        return [
            'type' => ['required', 'string', Rule::in(UploadJobType::values())],
            'files' => ['required', 'array', 'min:1', 'max:'.$maxFiles],
            'files.*' => [
                'required',
                'file',
                File::types($extensions)->max($maxKb),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => __('merchant.uploads.validation.type_required'),
            'type.in' => __('merchant.uploads.validation.type_invalid'),
            'files.required' => __('merchant.uploads.validation.files_required'),
            'files.min' => __('merchant.uploads.validation.files_required'),
            'files.max' => __('merchant.uploads.validation.files_max'),
            'files.*.required' => __('merchant.uploads.validation.file_missing'),
            'files.*.file' => __('merchant.uploads.validation.file_invalid'),
            'files.*.mimes' => __('merchant.uploads.validation.file_type_invalid'),
            'files.*.max' => __('merchant.uploads.validation.file_too_large'),
        ];
    }

    public function uploadType(): UploadJobType
    {
        return UploadJobType::from((string) $this->validated('type'));
    }
}
