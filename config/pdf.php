<?php

declare(strict_types=1);
use App\Services\Merchant\Pdf\Processors\LogisticsLabelsProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | PDF Engine — core settings (Milestone 2 foundation)
    |--------------------------------------------------------------------------
    |
    | Module-specific normalization — logistics labels implemented in M2.
    | Canvas dimensions default from domain preview settings when not set here.
    |
    */

    'temp_disk' => env('PDF_TEMP_DISK', 'temp'),

    'output_ttl_minutes' => (int) env('PDF_OUTPUT_TTL_MINUTES', 10),

    'download_grace_seconds' => (int) env('PDF_DOWNLOAD_GRACE_SECONDS', 30),

    'shred_on_download' => (bool) env('PDF_SHRED_ON_DOWNLOAD', true),

    'max_source_bytes' => (int) env('PDF_MAX_SOURCE_BYTES', 52_428_800), // 50 MB

    'max_pages_per_job' => (int) env('PDF_MAX_PAGES_PER_JOB', 500),

    /*
    |--------------------------------------------------------------------------
    | Canvas defaults (mm) — overridden by MerchantConfig preview settings
    |--------------------------------------------------------------------------
    */

    'canvas' => [
        'width_mm' => 150.0,
        'height_mm' => 100.0,
        'safe_zone_inset_mm' => 5.0,
        'aspect_ratio' => 1.5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Thermal label A4 output layout (logistics labels module)
    |--------------------------------------------------------------------------
    |
    | Single source label  → one A4 page with one normalized label + padding.
    | Multiple labels      → A4 sheets split 2×2 (four quadrants), up to 4 labels
    |                        per page; unused quadrants stay blank.
    |
    */

    'a4_output' => [
        'page_width_mm' => 210.0,
        'page_height_mm' => 297.0,
        'label' => [
            'width_mm' => 105.0,
            'height_mm' => 148.0,
            'safe_zone_inset_mm' => 0.0,
        ],
        'single' => [
            'padding_left_mm' => (float) env('PDF_A4_SINGLE_PADDING_LEFT_MM', 10.0),
            'padding_top_mm' => (float) env('PDF_A4_SINGLE_PADDING_TOP_MM', 10.0),
        ],
        'multi' => [
            'columns' => 2,
            'rows' => 2,
            'labels_per_page' => 4,
            'padding_left_mm' => (float) env('PDF_A4_MULTI_PADDING_LEFT_MM', 0.0),
            'padding_top_mm' => (float) env('PDF_A4_MULTI_PADDING_TOP_MM', 0.0),
            'center_grid_on_page' => (bool) env('PDF_A4_MULTI_CENTER_GRID', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation thresholds — used by PdfValidationService (future modules)
    |--------------------------------------------------------------------------
    */

    'validation' => [
        'aspect_tolerance_percent' => 10.0,
        'a4_width_mm' => 210.0,
        'a4_height_mm' => 297.0,
        'a4_tolerance_mm' => 3.0,
        'thermal_min_width_mm' => 90.0,
        'thermal_max_width_mm' => 110.0,
        'thermal_min_height_mm' => 140.0,
        'thermal_max_height_mm' => 160.0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Temp storage path templates (relative to temp disk root)
    |--------------------------------------------------------------------------
    */

    'paths' => [
        'job_root' => 'merchants/{merchant_id}/jobs/{job_id}',
        'sources' => 'merchants/{merchant_id}/jobs/{job_id}/sources',
        'work' => 'merchants/{merchant_id}/jobs/{job_id}/work',
        'outputs' => 'merchants/{merchant_id}/jobs/{job_id}/outputs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported processing modes (maps to UploadJobType in later phases)
    |--------------------------------------------------------------------------
    */

    'modes' => [
        'thermal_label' => [
            'enabled' => true,
            'processor' => LogisticsLabelsProcessor::class,
        ],
        'order_pdf_merge' => [
            'enabled' => true,
            'processor' => null,
        ],
        'delivery_label' => [
            'enabled' => true,
            'processor' => null,
        ],
        'picking_list_export' => [
            'enabled' => true,
            'processor' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | FPDI integration
    |--------------------------------------------------------------------------
    */

    'fpdi' => [
        'default_orientation' => 'P',
        'unit' => 'mm',
    ],

];
