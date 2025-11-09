<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for the WhatsApp API gateway.
    | These values are pulled from your .env file.
    |
    */

    'url' => env('WHATSAPP_API_URL', 'http://wa.mpdev.my.id/api'),

    'app_key' => env('WHATSAPP_API_APP_KEY'),

    'auth_key' => env('WHATSAPP_API_AUTH_KEY'),
];
