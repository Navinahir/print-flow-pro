<?php

declare(strict_types=1);

namespace App\Enums;

enum PdfValidationCode: string
{
    case Valid = 'valid';
    case MissingSource = 'missing_source';
    case FileNotReadable = 'file_not_readable';
    case FileTooLarge = 'file_too_large';
    case UnsupportedMode = 'unsupported_mode';
    case InvalidPdf = 'invalid_pdf';
    case PageLimitExceeded = 'page_limit_exceeded';
    case A4Rejected = 'a4_rejected';
    case ThermalSizeRejected = 'thermal_size_rejected';
    case AspectRatioWarning = 'aspect_ratio_warning';

    public function message(): string
    {
        return match ($this) {
            self::Valid => __('merchant.pdf.validation.valid'),
            self::MissingSource => __('merchant.pdf.validation.missing_source'),
            self::FileNotReadable => __('merchant.pdf.validation.file_not_readable'),
            self::FileTooLarge => __('merchant.pdf.validation.file_too_large'),
            self::UnsupportedMode => __('merchant.pdf.validation.unsupported_mode'),
            self::InvalidPdf => __('merchant.pdf.validation.invalid_pdf'),
            self::PageLimitExceeded => __('merchant.pdf.validation.page_limit_exceeded'),
            self::A4Rejected => __('merchant.pdf.validation.a4_rejected'),
            self::ThermalSizeRejected => __('merchant.pdf.validation.thermal_size_rejected'),
            self::AspectRatioWarning => __('merchant.pdf.validation.aspect_ratio_warning'),
        };
    }
}
