<?php

declare(strict_types=1);

namespace App\Http\Requests\Merchant;

use App\Services\Merchant\ThemeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateThemeRequest extends FormRequest
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
        return [
            'theme' => ['required', 'string', Rule::in(app(ThemeService::class)->supportedPreferences())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'theme.required' => __('merchant.theme.validation.required'),
            'theme.in' => __('merchant.theme.validation.unsupported'),
        ];
    }
}
