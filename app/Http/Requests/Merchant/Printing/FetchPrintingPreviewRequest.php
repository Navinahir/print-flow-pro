<?php

declare(strict_types=1);

namespace App\Http\Requests\Merchant\Printing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FetchPrintingPreviewRequest extends FormRequest
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
            'module' => ['required', 'string', Rule::in([
                'order_details',
                'logistics_labels',
                'picking_list',
                'delivery_labels',
            ])],
            'item_id' => ['required', 'string', 'max:120'],
        ];
    }
}
