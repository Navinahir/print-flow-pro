<?php

declare(strict_types=1);

return [

    'nav' => [
        'account' => 'Account',
        'profile' => 'Profile',
        'logout' => 'Log out',
    ],

    'sidebar' => [
        'footer_label' => 'Account actions',
    ],

    'profile' => [
        'information' => [
            'title' => 'Profile information',
            'description' => 'Update your account profile information and email address.',
            'name' => 'Name',
            'name_placeholder' => 'Enter your full name',
            'email' => 'Email address',
            'email_placeholder' => 'you@shop.example',
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
    ],

    'unauthorized' => [
        'title' => 'Access not allowed',
        'heading' => 'You are not authorised to access this page',
        'message' => 'This admin workspace is only available through the authorised management URL. If you need access, contact your system administrator.',
        'status' => '403 Forbidden',
    ],

];
