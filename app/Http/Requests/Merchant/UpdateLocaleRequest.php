<?php

declare(strict_types=1);

namespace App\Http\Requests\Merchant;

use App\Services\Merchant\LocaleService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var LocaleService $localeService */
        $localeService = app(LocaleService::class);

        $codes = $localeService->availableLocales()
            ->pluck('code')
            ->all();

        return [
            'locale' => ['required', 'string', Rule::in($codes)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'locale.required' => __('merchant.locale.validation.required'),
            'locale.in' => __('merchant.locale.validation.unsupported'),
        ];
    }
}
