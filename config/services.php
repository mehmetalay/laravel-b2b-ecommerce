<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sms_explorer' => [
        'username' => env('SMS_EXPLORER_USERNAME'),
        'password' => env('SMS_EXPLORER_PASSWORD'),
        'from' => env('SMS_EXPLORER_FROM'),
        'endpoint' => env('SMS_EXPLORER_ENDPOINT', 'https://sgw.maradit.net/api/xml/reply/Submit'),
    ],

    'erp' => [
        'base_url' => env('ERP_BASE_URL', env('ERP_HOST') . '/api'),
    ],

    'notifications' => [
        'error_mail' => env('ERROR_NOTIFICATION_MAIL'),
    ],

];
