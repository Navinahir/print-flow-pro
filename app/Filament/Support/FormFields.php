<?php

declare(strict_types=1);

namespace App\Filament\Support;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;

final class FormFields
{
    public static function text(
        string $name,
        string $label,
        bool $required = false,
        ?string $placeholder = null,
        ?int $maxLength = 255,
    ): TextInput {
        $field = TextInput::make($name)
            ->label($label)
            ->placeholder($placeholder ?? $label)
            ->maxLength($maxLength);

        if ($required) {
            $field->required();
        }

        return $field;
    }

    /**
     * @template T of Field
     *
     * @param  T  $field
     * @return T
     */
    public static function applyCommon(Field $field, string $label, bool $required = false, ?string $placeholder = null): Field
    {
        $field->label($label);

        if ($placeholder !== null && method_exists($field, 'placeholder')) {
            $field->placeholder($placeholder);
        }

        if ($required) {
            $field->required();
        }

        return $field;
    }
}
