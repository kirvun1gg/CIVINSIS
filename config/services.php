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

    'groq' => [
        'key'   => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'qwen/qwen3.8-27b'),
        'url'   => env('GROQ_URL', 'https://api.groq.com/openai/v1/chat/completions'),
    ],

    'deepl' => [
        'key' => env('DEEPL_API_KEY'),
        'url' => env('DEEPL_API_URL', 'https://api-free.deepl.com/v2/translate'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
        // En local, PHP en Windows/WAMP normalmente no trae un bundle de
        // certificados CA configurado, así que cURL falla al verificar el
        // TLS de Google (el mismo problema que ya se vio con Groq y DeepL
        // en este entorno). Se desactiva solo en local, igual que en esos.
        // (Ojo: dentro de archivos de config hay que usar env(), nunca
        // app()->environment() — el contenedor aún no tiene el entorno
        // detectado en este punto tan temprano del arranque.)
        'guzzle' => env('APP_ENV') === 'local' ? ['verify' => false] : [],
    ],

];
