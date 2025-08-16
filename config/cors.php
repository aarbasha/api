<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'broadcasting/auth', 'chat_media/*', 'cover/*', 'images/*', 'avatars/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:3000', 'https://api.anasarbasha.net','https://api.anasarbasha.net/*' ,  'https://api.anasarbasha.de', 'https://anasarbasha.net', 'https://anasarbasha.net/*', 'https://anasarbasha.de', 'https://anasarbasha.de/*'], // تأكد من إضافة المصدر هنا


    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
