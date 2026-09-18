<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Analiza imágenes subidas por usuarios (avatar, portada de propuesta) con
 * un modelo de visión de Groq, antes de guardarlas, para detectar contenido
 * no apto (desnudez, violencia gráfica, odio, armas, etc.).
 */
class ImageModerationService
{
    private const PROMPT = <<<TXT
Eres un moderador de contenido para una plataforma de participación ciudadana salvadoreña dirigida a jóvenes.

Analiza la imagen adjunta y determina si contiene:
- Desnudez o contenido sexual explícito
- Violencia gráfica, sangre o mutilación
- Símbolos de odio, discriminación o extremismo
- Armas de fuego o contenido que incite a la violencia
- Cualquier otro contenido no apto para una audiencia juvenil

Responde ÚNICAMENTE en este formato JSON exacto (sin markdown, sin explicaciones extra):
{
  "inapropiada": true o false,
  "razon": "descripción breve del problema o 'ninguno'",
  "severidad": "baja|media|alta"
}
TXT;

    /**
     * Analiza una imagen (data URI base64, ej. "data:image/png;base64,...").
     * Devuelve: inapropiada (bool), razon (string), severidad (string).
     * Si la IA no está disponible o falla, deja pasar la imagen (no bloquea por error).
     */
    public function analizar(string $imagenBase64): array
    {
        $pase = ['inapropiada' => false, 'razon' => '', 'severidad' => 'baja'];

        $key = config('services.groq.key');
        if (empty($key) || $imagenBase64 === '') return $pase;

        try {
            $http = Http::timeout(30)->withToken($key)->acceptJson();
            if (app()->environment('local')) {
                $http = $http->withoutVerifying();
            }

            $resp = $http->post(config('services.groq.url'), [
                'model'       => config('services.groq.model'),
                'messages'    => [[
                    'role'    => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => self::PROMPT],
                        ['type' => 'image_url', 'image_url' => ['url' => $imagenBase64]],
                    ],
                ]],
                'temperature' => 0.2,
                'max_tokens'  => 200,
            ]);

            if ($resp->successful()) {
                $texto = $resp->json('choices.0.message.content');
                $json  = json_decode((string) $texto, true);

                if (is_array($json) && isset($json['inapropiada'])) {
                    return [
                        'inapropiada' => (bool) $json['inapropiada'],
                        'razon'       => $json['razon'] ?? 'Contenido inapropiado',
                        'severidad'   => in_array($json['severidad'] ?? '', ['baja', 'media', 'alta'])
                                        ? $json['severidad'] : 'media',
                    ];
                }
            } else {
                Log::warning('Groq vision respondió error', ['status' => $resp->status(), 'body' => $resp->body()]);
            }
        } catch (\Throwable $e) {
            Log::error('Error llamando a Groq vision: ' . $e->getMessage());
        }

        return $pase;
    }
}
