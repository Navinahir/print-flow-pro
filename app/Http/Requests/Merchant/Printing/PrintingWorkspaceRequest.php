<?php

declare(strict_types=1);

namespace App\Http\Requests\Merchant\Printing;

use Illuminate\Foundation\Http\FormRequest;

class PrintingWorkspaceRequest extends FormRequest
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
            'item_id' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
