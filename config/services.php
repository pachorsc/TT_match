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

    'youtube' => [
        'api_key' => env('YOUTUBE_API_KEY'),
        'channel_id' => env('YOUTUBE_CHANNEL_ID', 'UC9ckyA_A3MfXUa0ttxMoIZw'),
    ],

    'wtt' => [
        'api_key' => env('WTT_API_KEY'),
        'sec_api_key' => env('WTT_SEC_API_KEY'),
    ],

];
