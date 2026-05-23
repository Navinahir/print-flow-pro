<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\UploadJobType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreUploadRequest extends FormRequest
{
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
        $maxKb = config('printflow.upload.max_file_size_kb', 20480);
        $maxFiles = config('printflow.upload.max_files_per_job', 20);

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
            'type.required' => 'Please select an upload type.',
            'type.in' => 'The selected upload type is invalid.',
            'files.required' => 'Please add at least one file to upload.',
            'files.min' => 'Please add at least one file to upload.',
            'files.max' => 'You may upload up to :max files at once.',
            'files.*.required' => 'One or more files are missing.',
            'files.*.file' => 'Each item must be a valid file.',
            'files.*.mimes' => 'This file type is not allowed for the selected upload type.',
            'files.*.max' => 'Each file may not be larger than :max kilobytes.',
        ];
    }

    public function uploadType(): UploadJobType
    {
        return UploadJobType::from((string) $this->validated('type'));
    }
}
