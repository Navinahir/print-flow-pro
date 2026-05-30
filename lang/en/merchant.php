<?php

declare(strict_types=1);

return [

    'brand' => [
        'name' => 'XY Cubic Shopee',
        'tagline' => 'Print-ready workflows for Shopee sellers',
    ],

    'errors' => [
        'region_inactive' => 'This region is not activated yet.',
    ],

    'general' => [
        'not_available' => '—',
    ],

    'nav' => [
        'dashboard' => 'Dashboard',
        'operations' => 'Operations',
        'uploads' => 'Uploads',
        'upload_history' => 'Upload history',
        'new_upload' => 'New upload',
        'printing' => 'Printing',
        'printing_modules' => 'Printing modules',
        'order_details' => 'Order details',
        'logistics_labels' => 'Logistics labels',
        'picking_list' => 'Picking list',
        'delivery_labels' => 'Delivery labels',
        'coming_soon' => 'Coming soon',
        'account' => 'Account',
        'profile' => 'Profile',
        'logout' => 'Log out',
        'open_menu' => 'Open navigation menu',
        'close_menu' => 'Close navigation menu',
        'toggle_sidebar' => 'Toggle sidebar',
        'collapse_sidebar' => 'Collapse sidebar',
        'expand_sidebar' => 'Expand sidebar',
    ],

    'sidebar' => [
        'footer_label' => 'Account actions',
    ],

    'form' => [
        'required_indicator' => 'required',
    ],

    'header' => [
        'welcome' => 'Welcome, :name',
        'merchant_label' => 'Merchant',
    ],

    'locale' => [
        'switcher_label' => 'Language',
        'validation' => [
            'required' => 'Please select a language.',
            'unsupported' => 'This language is not available for your region.',
        ],
    ],

    'theme' => [
        'switcher_label' => 'Theme',
        'light' => 'Light mode',
        'dark' => 'Dark mode',
        'system' => 'System preference',
        'updated' => 'Theme preference saved.',
        'validation' => [
            'required' => 'Please select a theme.',
            'unsupported' => 'This theme option is not supported.',
        ],
    ],

    'user_menu' => [
        'label' => 'Account menu',
    ],

    'footer' => [
        'copyright' => '© :year :brand. All rights reserved.',
        'help' => 'Help & support',
        'privacy' => 'Privacy',
        'terms' => 'Terms',
    ],

    'breadcrumb' => [
        'home' => 'Home',
        'aria_label' => 'Breadcrumb',
    ],

    'components' => [
        'page_header' => [
            'actions' => 'Page actions',
        ],
        'empty_state' => [
            'default_title' => 'Nothing here yet',
            'default_description' => 'Get started by creating your first item.',
        ],
        'loading_state' => [
            'default_message' => 'Loading…',
            'aria_label' => 'Loading content',
        ],
        'page_loader' => [
            'content_message' => 'Loading content…',
            'message' => 'Updating language…',
            'aria_label' => 'Loading page',
        ],
    ],

    'dashboard' => [
        'title' => 'Dashboard',
        'subtitle' => 'Overview of your print workspace',
        'welcome' => 'Welcome back, :name.',
        'merchant_account' => 'Merchant account: :name',
        'cards' => [
            'new_upload' => [
                'title' => 'New upload',
                'description' => 'Upload PDFs, CSV, or XLSX for processing.',
            ],
            'upload_history' => [
                'title' => 'Upload history',
                'description' => 'Track status and view uploaded files.',
            ],
            'printing' => [
                'title' => 'Printing modules',
                'description' => 'Open a workspace to preview and print fulfillment documents.',
            ],
        ],
        'stats' => [
            'recent_uploads' => 'Recent uploads',
            'pending_jobs' => 'Pending jobs',
            'completed_jobs' => 'Completed jobs',
        ],
    ],

    'uploads' => [
        'title' => 'Upload history',
        'create_title' => 'New upload',
        'show_title' => 'Upload #:id',
        'subtitle' => 'Manage your file uploads and processing status',
        'create_subtitle' => 'Select a type and drop your files',
        'show_subtitle' => 'Upload details and file list',
        'new_upload' => 'New upload',
        'back_to_history' => 'Back to history',
        'empty' => [
            'title' => 'No uploads yet',
            'description' => 'Upload your first files to start processing.',
            'action' => 'Upload your first files',
        ],
        'table' => [
            'id' => 'ID',
            'type' => 'Type',
            'status' => 'Status',
            'files' => 'Files',
            'uploaded_by' => 'Uploaded by',
            'date' => 'Date',
            'actions' => 'Actions',
            'view' => 'View',
        ],
        'form' => [
            'type_label' => 'Upload type',
            'type_placeholder' => 'Select type…',
            'accepted_pdf' => 'Accepted: PDF',
            'accepted_spreadsheet' => 'Accepted: CSV, XLS, XLSX',
            'dropzone_title' => 'Drag & drop files here, or browse',
            'dropzone_browse' => 'browse',
            'dropzone_limits' => 'Max :count files · :size MB each',
            'uploading' => 'Uploading… please wait.',
            'cancel' => 'Cancel',
            'submit' => 'Upload files',
        ],
        'detail' => [
            'type' => 'Type',
            'status' => 'Status',
            'uploaded_by' => 'Uploaded by',
            'file_count' => 'File count',
            'pdf_files' => 'PDF files',
            'spreadsheet_files' => 'Spreadsheet files',
            'preview_placeholder' => 'Preview is shown in the panel on the right.',
        ],
        'preview' => [
            'heading' => 'Upload preview',
            'description' => ':width×:height mm print preview for this upload job.',
            'refresh' => 'Refresh preview',
            'refreshing' => 'Refreshing…',
            'retry' => 'Try again',
            'unavailable' => 'Preview is not available for this upload yet.',
            'error_title' => 'Could not load preview',
            'empty_title' => 'Preview not ready',
            'empty_description' => 'Processing has not produced a printable preview for this job yet.',
        ],
        'status' => [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
        ],
        'types' => [
            'order_pdf' => 'Order PDF',
            'thermal_label' => 'Thermal Label',
            'picking_list' => 'Picking List',
            'delivery_label' => 'Delivery Label',
        ],
        'validation' => [
            'type_required' => 'Please select an upload type.',
            'type_invalid' => 'The selected upload type is invalid.',
            'files_required' => 'Please add at least one file to upload.',
            'files_max' => 'You may upload up to :max files at once.',
            'file_missing' => 'One or more files are missing.',
            'file_invalid' => 'Each item must be a valid file.',
            'file_type_invalid' => 'This file type is not allowed for the selected upload type.',
            'file_too_large' => 'Each file may not be larger than :max kilobytes.',
        ],
        'errors' => [
            'no_merchant_profile' => 'Your account is not linked to a merchant profile. Please contact support.',
        ],
    ],

    'profile' => [
        'title' => 'Profile',
        'subtitle' => 'Manage your account settings',
        'information' => [
            'title' => 'Profile information',
            'description' => 'Update your account profile information and email address.',
            'name' => 'Name',
            'name_placeholder' => 'Enter your full name',
            'email' => 'Email address',
            'email_placeholder' => 'you@shop.example',
            'unverified' => 'Your email address is unverified.',
            'resend_verification' => 'Click here to re-send the verification email.',
            'verification_sent' => 'A new verification link has been sent to your email address.',
            'save' => 'Save',
            'saved' => 'Saved.',
        ],
        'password' => [
            'title' => 'Update password',
            'description' => 'Ensure your account is using a long, random password to stay secure.',
            'current' => 'Current password',
            'current_placeholder' => 'Enter your current password',
            'new' => 'New password',
            'new_placeholder' => 'Enter a new password',
            'confirm' => 'Confirm password',
            'confirm_placeholder' => 'Confirm your new password',
            'save' => 'Save',
            'saved' => 'Saved.',
        ],
        'photo' => [
            'title' => 'Profile picture',
            'description' => 'Upload a photo to personalize your account. It appears in the header and sidebar.',
            'upload' => 'Upload photo',
            'remove' => 'Remove photo',
            'hint' => 'JPEG, PNG, or WebP up to 2 MB. You can crop before saving.',
            'crop_title' => 'Crop profile picture',
            'crop_description' => 'Drag to reposition and use the handles to adjust the crop area.',
            'cancel' => 'Cancel',
            'save' => 'Save photo',
            'saving' => 'Saving…',
            'updated' => 'Profile picture updated.',
            'removed' => 'Profile picture removed.',
        ],
        'delete' => [
            'title' => 'Delete account',
            'description' => 'Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.',
            'button' => 'Delete account',
            'confirm_title' => 'Are you sure you want to delete your account?',
            'confirm_text' => 'Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm.',
            'password' => 'Password',
            'confirm_button' => 'Delete account',
            'cancel' => 'Cancel',
        ],
    ],

    'printing' => [
        'section_title' => 'Printing modules',
        'dashboard_description' => 'Choose a module to preview labels, orders, and picking lists before printing.',
        'modules_available' => '{1} :count module available|[2,*] :count modules available',
        'nav_none_enabled' => 'No printing modules are enabled for this region.',
        'errors' => [
            'module_disabled' => 'This printing module is not enabled for your region.',
        ],
        'workspace' => [
            'list_heading' => 'Items',
            'list_description' => 'Select an item to preview printable content.',
            'list_empty' => 'No items available yet.',
            'preview_heading' => 'Preview',
            'preview_description' => 'Print preview workspace (150×100 mm canvas coming soon).',
            'preview_placeholder' => 'Select an item from the list to preview',
            'preview_not_implemented' => 'Print actions will be available in a future phase.',
            'print' => 'Print',
            'print_disabled_hint' => 'Print will be available after preview engine is implemented.',
            'status_pending' => 'Pending',
            'status_processing' => 'Processing',
            'status_ready' => 'Ready',
            'placeholder_item_title' => 'Sample item',
            'placeholder_item_subtitle' => '1500×1000 px — matches 3:2 print ratio.',
            'placeholder_item_invalid_title' => 'Sample item (wrong ratio)',
            'placeholder_item_invalid_subtitle' => '800×600 px — triggers aspect ratio warning.',
        ],
        'preview' => [
            'not_found' => 'Preview content could not be loaded.',
            'order_details' => [
                'fields' => [
                    'customer' => 'Customer',
                    'date' => 'Order date',
                    'sku' => 'SKU',
                    'item' => 'Item',
                    'qty' => 'Qty',
                    'price' => 'Price',
                    'subtotal' => 'Subtotal',
                    'shipping' => 'Shipping',
                    'total' => 'Total',
                ],
                'samples' => [
                    'list_title' => 'Order #:id',
                    'list_subtitle' => 'Shopee order — printable summary',
                    'list_subtitle_alt' => 'Second sample order',
                    'order_number' => 'SO-2026-:id',
                    'customer_name' => 'Chen Wei-Lin',
                    'order_date' => '2026-05-28',
                    'status' => 'Ready to print',
                    'item_one' => 'Wireless earbuds case',
                    'item_two' => 'USB-C charging cable',
                    'notes' => 'Gift wrap requested. Include invoice copy.',
                ],
            ],
            'logistics_labels' => [
                'fields' => [
                    'carrier' => 'Carrier',
                    'tracking' => 'Tracking',
                    'shipment_date' => 'Ship date',
                    'service_level' => 'Service',
                ],
                'samples' => [
                    'list_title' => 'Logistics label sample',
                    'list_subtitle' => 'Thermal label with barcode area',
                    'tracking_number' => 'TW:id234567890',
                    'carrier' => 'Black Cat',
                    'recipient_name' => 'Lin Mei-Hua',
                    'recipient_address' => 'No. 88, Zhongxiao East Rd, Taipei 100',
                    'shipment_date' => '2026-05-29',
                    'service_level' => 'Standard home delivery',
                ],
            ],
            'picking_list' => [
                'fields' => [
                    'sku' => 'SKU',
                    'item' => 'Item',
                    'location' => 'Bin',
                    'qty' => 'Qty',
                    'total_units' => 'Total units',
                ],
                'samples' => [
                    'list_title' => 'Picking list PL-:id',
                    'list_subtitle' => 'Warehouse pick sheet preview',
                    'list_reference' => 'PL-2026-:id',
                    'warehouse' => 'Taipei Main Warehouse',
                    'pick_date' => '2026-05-29',
                    'item_one' => 'Wireless earbuds case',
                    'item_two' => 'USB-C charging cable',
                    'item_three' => 'Screen protector pack',
                ],
            ],
        ],
        'modules' => [
            'order_details' => [
                'title' => 'Order details',
                'subtitle' => 'Review order PDFs and prepare print-ready outputs.',
            ],
            'logistics_labels' => [
                'title' => 'Logistics labels',
                'subtitle' => 'Normalize and print Shopee logistics labels.',
            ],
            'picking_list' => [
                'title' => 'Picking list',
                'subtitle' => 'Aggregate picking lists for warehouse fulfillment.',
            ],
            'delivery_labels' => [
                'title' => 'Delivery labels',
                'subtitle' => 'Generate delivery labels for outbound shipments.',
            ],
        ],
    ],

    'flash' => [
        'upload_received' => 'Your files were received successfully. Processing will begin shortly.',
        'profile_updated' => 'Your profile has been updated.',
        'locale_updated' => 'Language preference updated.',
        'theme_updated' => 'Theme preference updated.',
        'success' => 'Success',
        'error' => 'Error',
        'warning' => 'Warning',
        'info' => 'Information',
    ],

    'ajax' => [
        'error_default' => 'Something went wrong. Please try again.',
        'network_error' => 'Network error. Check your connection and try again.',
    ],

    'sweetalert' => [
        'confirm' => 'Confirm',
        'cancel' => 'Cancel',
        'ok' => 'OK',
    ],

    'preview' => [
        'dimensions_label' => ':width×:height mm',
        'toolbar' => [
            'heading' => 'Preview',
            'description' => 'Fixed 150×100 mm print area (3:2 ratio). Scales responsively to fit your screen.',
            'print' => 'Print',
            'print_disabled_hint' => 'Select an item to enable printing.',
            'safe_zone_disabled_hint' => 'Select an item to toggle the safe zone.',
        ],
        'container' => [
            'aria_label' => 'Print preview canvas, 150 by 100 millimetres',
        ],
        'safe_zone' => [
            'aria_label' => 'Safe print zone, :inset millimetre inset from each edge',
            'toggle_show' => 'Show safe zone',
            'toggle_hide' => 'Hide safe zone',
            'description' => 'Dashed guide indicates the printable safe area inset :inset mm from each edge.',
        ],
        'empty' => [
            'title' => 'Select an item from the list to preview',
            'description' => 'The preview canvas shows a fixed 150×100 mm workspace scaled to your screen.',
            'selected_fallback' => 'Preview item',
            'content_placeholder' => 'Select an item from the list to view its printable preview.',
            'list_hint' => 'Choose an item from the list on the left',
        ],
        'aspect_ratio' => [
            'valid' => 'Asset dimensions match the 150×100 mm (3:2) print ratio.',
            'invalid' => 'Aspect ratio deviates by :deviation% from 3:2 (tolerance :tolerance%).',
            'banner_title' => 'Aspect ratio warning',
            'banner_message' => 'This asset does not match the required 150×100 mm (3:2) print ratio.',
            'force_adjustment' => 'Force adjustment (proceed anyway)',
            'sweetalert_title' => 'Aspect ratio mismatch',
            'sweetalert_message' => 'The selected asset exceeds the allowed deviation from the 150×100 mm print ratio.',
            'validation' => [
                'width_required' => 'Width is required when no file is uploaded.',
                'height_required' => 'Height is required when no file is uploaded.',
                'file_or_dimensions_required' => 'Provide either width and height or an image file.',
                'unsupported_file' => 'Only image files (JPG, PNG, GIF, WebP, BMP) are supported.',
            ],
        ],
    ],

    'delivery_labels' => [
        'preview' => [
            'remarks_heading' => 'Remarks',
            'shrunk_hint' => 'Address auto-shrunk to fit label',
        ],
        'csv' => [
            'list_description' => 'Upload a CSV or select a label to preview.',
            'upload_label' => 'Import delivery labels (CSV)',
            'choose_file' => 'Choose CSV file',
            'upload_hint' => 'Required columns: recipient and/or address. Optional: remarks, tracking, carrier.',
            'uploading' => 'Importing CSV…',
            'list_empty' => 'No delivery labels yet. Upload a CSV to get started.',
            'list_subtitle' => 'Label #:id',
            'unknown_recipient' => 'Unknown recipient',
            'fallback_address' => 'Address not provided',
            'confirm_title' => 'Import delivery labels?',
            'confirm_message' => 'This will parse the CSV and add labels to your workspace.',
            'import_success' => ':count delivery label(s) imported successfully.',
            'validation' => [
                'file_required' => 'Please choose a CSV file to upload.',
                'file_type' => 'Only CSV files are supported.',
                'file_too_large' => 'The CSV file is too large.',
                'headers_missing' => 'The CSV file has no header row.',
                'columns_missing' => 'Could not detect recipient or address columns in the CSV.',
                'rows_missing' => 'The CSV file contains no data rows.',
                'no_valid_rows' => 'No valid delivery label rows were found in the CSV.',
            ],
        ],
        'samples' => [
            'short_title' => 'Standard address',
            'short_subtitle' => '18 px — within 35 character threshold',
            'short_recipient' => 'Chen Wei-Lin',
            'short_address' => 'No. 88, Zhongxiao East Rd, Taipei',
            'short_remarks' => 'Leave at concierge if unavailable.',

            'long_title' => 'Long courier address',
            'long_subtitle' => 'Auto-shrunk toward 14 px floor',
            'long_recipient' => 'Lin Mei-Hua',
            'long_address' => 'No. 188, Section 5, Xinyi Road, Xinyi District, Taipei City 110, Taiwan — deliver to rear entrance near parking lot B, contact security desk on arrival',
            'long_remarks' => 'Fragile items. Call recipient 10 minutes before delivery.',

            'multiline_title' => 'Multi-line address',
            'multiline_subtitle' => 'Wrapped lines with remarks pushed down',
            'multiline_recipient' => 'Wang Jia-Hao',
            'multiline_address' => "Floor 12, No. 200, Keelung Road\nXinyi District, Taipei City 110",
            'multiline_remarks' => 'Office hours: Mon–Fri 09:00–18:00 only.',
        ],
    ],

];
