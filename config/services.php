<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'facebook' => [
       'client_id' => '1761892490797537',
       'client_secret' => 'e53c1912a892eca58e8dc5dba9aa00b6',
       'redirect' => 'http://prod.samlorgo.com/en/auth/facebook/callback',
    ],

    'google' => [
       'client_id' => '294541376541-qk41fq7b1jandhlhgb8o73bja32n1bau.apps.googleusercontent.com',
       'client_secret' => 'MkI5yjdsvHR1q6WEzFnVm1JT',
       'redirect' => 'http://prod.samlorgo.com/en/auth/google/callback',
    ],
    'youtubekey' => env('YOUTUBE_KEY'),
    'youtube_embed_url' => 'https://www.youtube.com/embed/',
    'vimeo_embed_url' => 'https://player.vimeo.com/video/',
    'facebook_api_key'=>env('FACEBOOK_APP_KEY'),
    'aviary_api_key'=>env('AVIARY_KEY'),
    'fb_app_version'=>env('FB_VERSION'),
    's3_storage_enabled'=>env('S3_STORAGE_ENABLED'),
    'aws_img_path'=>env('AMAZON_AWS_URL'),
    'pdf_api_url'=>env('PDF_API_URL'),
    'uploaded_image_server_auth_key'=>'Authorization: Bearer '.env('UPLOADED_IMAGE_SERVER_AUTH_KEY'),



];
