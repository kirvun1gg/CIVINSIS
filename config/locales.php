<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Idiomas soportados por CIVINSIS
    |--------------------------------------------------------------------------
    |
    | 'es' es siempre el idioma original de TODO el contenido generado por
    | usuarios (propuestas, debates, comentarios, respuestas). Los demás se
    | traducen bajo demanda vía DeepL (ver App\Services\TranslationService).
    | Añadir un idioma nuevo aquí + sus archivos en resources/lang/<code>/
    | es suficiente para activarlo, sin tocar código.
    |
    */

    'supported' => [
        'es' => ['nombre' => 'Español', 'bandera' => '🇪🇸'],
        'en' => ['nombre' => 'English', 'bandera' => '🇺🇸'],
        'fr' => ['nombre' => 'Français', 'bandera' => '🇫🇷'],
    ],

    'default' => 'es',

];
