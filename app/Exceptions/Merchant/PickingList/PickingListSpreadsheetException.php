<?php

declare(strict_types=1);

namespace App\Exceptions\Merchant\PickingList;

use App\Exceptions\Merchant\Pdf\PdfNormalizationException;

class PickingListSpreadsheetException extends PdfNormalizationException
{
    public static function invalidFormat(?string $detail = null): self
    {
        return new self(__('merchant.picking_list.errors.invalid_format', [
            'detail' => $detail ?? '',
        ]));
    }

    public static function missingColumns(string $columns): self
    {
        return new self(__('merchant.picking_list.errors.missing_columns', [
            'columns' => $columns,
        ]));
    }

    public static function emptySpreadsheet(): self
    {
        return new self(__('merchant.picking_list.errors.empty_spreadsheet'));
    }
}
