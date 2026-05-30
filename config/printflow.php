<?php

return [

    'brand' => [
        'name' => env('PRINTFLOW_BRAND_NAME', 'XY Cubic Shopee'),
        'logo' => env('PRINTFLOW_BRAND_LOGO'),
        'favicon' => env('PRINTFLOW_BRAND_FAVICON'),
    ],

    'admin' => [
        'path' => env('PRINTFLOW_ADMIN_PATH', env('ADMIN_PATH_PREFIX', 'boss')),
    ],

    'upload' => [
        'max_file_size_kb' => (int) env('PRINTFLOW_UPLOAD_MAX_KB', 20480),
        'max_files_per_job' => (int) env('PRINTFLOW_UPLOAD_MAX_FILES', 20),
    ],

];
