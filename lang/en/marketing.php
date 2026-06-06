<?php

declare(strict_types=1);

return [

    'locales' => [
        'en' => 'English',
        'zh-TW' => 'Traditional Chinese',
    ],

    'locale_codes' => [
        'en' => 'EN',
        'zh-TW' => 'TW',
    ],

    'ui' => [
        'language' => 'Language',
        'toggle_color_mode' => 'Toggle color mode',
        'open_menu' => 'Open menu',
        'close_menu' => 'Close menu',
    ],

    'brand' => [
        'logo_alt' => 'XYCubic',
        'page_title_suffix' => 'Print-ready PDF workflows',
    ],

    'nav' => [
        'features' => 'Features',
        'pricing' => 'Pricing',
        'tutorials' => 'Tutorials',
        'resources' => 'Resources',
        'about_us' => 'About Us',
        'try_it_now' => 'Try it Now',
    ],

    'hero' => [
        'badge' => 'All-in-One Shipping Solution',
        'title_line_1' => 'Unified Shipping Management',
        'title_line_2' => 'Batch Print in One Place',
        'supports' => 'Supports Shopee / Multi-Platform Sellers',
        'description' => 'Integrate orders, pick lists, and shipping labels in one workflow.',
        'cta' => 'Get Started',
        'banner_image' => 'images/banner-en.png',
        'image_alt' => 'XYCubic dashboard preview',
        'pill_order' => 'Order Integration',
        'pill_shipping' => 'Shipping Integration',
        'pill_pick_list' => 'Pick List Statistics',
    ],

    'features' => [
        'title' => 'Core Features',
        'subtitle' => 'Designed for scale. Engineered for precision. The terminal for your logistics operations.',
        'cards' => [
            'orders' => [
                'title' => 'Batch Order Printing',
                'description' => 'Streamline multi-platform orders in one click.',
                'bullets' => [
                    'Merge & batch print carrier orders',
                    'Side-by-side real-time preview',
                    'No marketplace data stored',
                ],
            ],
            'labels' => [
                'title' => 'Batch Shipping Labels',
                'description' => 'Print mixed carrier labels in one unified workflow.',
                'bullets' => [
                    'Mixed-carrier batch printing',
                    'Supports A4 & 100×150 formats',
                    'Real-time shipping label preview',
                ],
            ],
            'picking' => [
                'title' => 'Smart Unified Pick Lists',
                'description' => 'Combine cross-carrier orders into one smart pick list.',
                'bullets' => [
                    'Automatic item quantity summary',
                    'One pick list for all carriers',
                    'No marketplace sales data stored',
                ],
            ],
        ],
    ],

    'faq' => [
        'title' => 'Frequently Asked Questions',
        'items' => [
            [
                'question' => 'Will using XYCubic risk my shop data leak or account penalty?',
                'answer' => 'Absolutely not. XYCubic strictly adheres to official API data protection standards. Built with our real-time streaming technology, all order and customer data are processed entirely in memory and cleared instantly upon printing. Zero data is stored on our servers.',
            ],
            [
                'question' => 'Can the Smart Pick List really combine orders from different carriers?',
                'answer' => 'Yes. XYCubic bypasses the traditional "one carrier per sheet" restriction. Our system automatically aggregates and combines item totals across all couriers into a single unified list, minimizing fulfillment foot traffic and errors.',
            ],
            [
                'question' => 'What printers and label formats are supported?',
                'answer' => 'We support 99% of standard desktop printers (A4) and all major thermal label printers (100×150 mm format). High-res barcodes render instantly via desktop or mobile, ensuring flawless scanning at carrier drop-offs.',
            ],
        ],
    ],

    'footer' => [
        'description' => 'Cross-store logistics integration expert. Consolidate multi-platform orders, batch printing, and smart picking for efficient fulfillment. Zero data retention, your secure logistics assistant.',
        'copyright' => '© :year XYCubic (PIXMA Business Co., Ltd.). All rights reserved.',
        'features' => 'Features',
        'resources' => 'Resources',
        'about_us' => 'About Us',
        'contact_us' => 'Contact Us',
        'links' => [
            'batch_print_orders' => 'Batch Print Orders',
            'batch_shipping_labels' => 'Batch Shipping Labels',
            'smart_picking_lists' => 'Smart Picking Lists',
            'order_integration' => 'Order Integration',
            'tutorials' => 'Tutorials',
            'faq' => 'FAQ',
            'company_profile' => 'Company Profile',
            'contact' => 'Contact Us',
            'privacy_policy' => 'Privacy Policy',
            'terms_of_service' => 'Terms of Service',
        ],
        'address' => '8F.-10, No. 5, Wuquan 1st Rd., Xinzhuang Dist., New Taipei City',
    ],

    'legal' => [
        'privacy' => [
            'title' => 'Privacy Policy',
            'sections' => [
                [
                    'heading' => 'Overview',
                    'body' => 'XY Cubic Shopee processes uploaded files locally to prepare print-ready outputs. We do not sell personal data to third parties.',
                ],
                [
                    'heading' => 'Data we collect',
                    'body' => 'Account information (name, email), uploaded files for processing, and usage logs required to operate the service.',
                ],
                [
                    'heading' => 'Data retention',
                    'body' => 'Uploaded files are stored temporarily for processing. Retention policies will be finalized before production billing launch.',
                ],
                [
                    'heading' => 'Contact',
                    'body' => 'For privacy questions, email service@xycubic.com.',
                ],
            ],
        ],
        'terms' => [
            'title' => 'Terms of Service',
            'sections' => [
                [
                    'heading' => 'Acceptance',
                    'body' => 'By using XY Cubic Shopee you agree to these terms and our privacy policy.',
                ],
                [
                    'heading' => 'Service scope',
                    'body' => 'The platform helps Shopee sellers prepare thermal labels, order PDFs, and picking lists. Shopee API integration is not included in the current phase.',
                ],
                [
                    'heading' => 'Acceptable use',
                    'body' => 'You must have rights to the files you upload. Do not upload unlawful content or attempt to bypass security controls.',
                ],
                [
                    'heading' => 'Contact',
                    'body' => 'For terms questions, email service@xycubic.com.',
                ],
            ],
        ],
    ],

];
