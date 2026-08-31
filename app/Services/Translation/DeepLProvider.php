<?php

namespace App\Services\Translation;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepLProvider implements TranslationProviderInterface
{
    /** Mapeo de locale de Laravel -> código de idioma que espera DeepL. */
    private const MAPA_LOCALE = [
        'es' => 'ES',
        'en' => 'EN-US',
        'fr' => 'FR',
    ];

    public function translate(string $text, string $targetLocale, ?string $sourceLocale = null): ?string
    {
        $resultado = $this->translateMany([$text], $targetLocale, $sourceLocale);
        return $resultado[0] ?? null;
    }

    public function translateMany(array $texts, string $targetLocale, ?string $sourceLocale = null): array
    {
        $textos = array_values($texts);
        if (empty($textos)) return [];

        $key = config('services.deepl.key');
        if (empty($key)) {
            // Sin key configurada: no hay traducción disponible (null), nunca se cachea como si lo fuera.
            return array_fill(0, count($textos), null);
        }

        try {
            $http = Http::timeout(15)->withHeaders([
                'Authorization' => 'DeepL-Auth-Key ' . $key,
            ]);

            if (app()->environment('local')) {
                $http = $http->withoutVerifying();
            }

            $body = [
                'text'        => $textos,
                'target_lang' => self::MAPA_LOCALE[$targetLocale] ?? strtoupper($targetLocale),
            ];
            if ($sourceLocale && isset(self::MAPA_LOCALE[$sourceLocale])) {
                $body['source_lang'] = self::MAPA_LOCALE[$sourceLocale];
            }

            $resp = $http->post(config('services.deepl.url'), $body);

            if ($resp->successful()) {
                $traducciones = $resp->json('translations', []);
                if (is_array($traducciones) && count($traducciones) === count($textos)) {
                    return array_map(fn ($t) => (string) ($t['text'] ?? ''), $traducciones);
                }
            }

            Log::warning('DeepL respondió error', ['status' => $resp->status()]);
        } catch (\Throwable $e) {
            Log::error('Error llamando a DeepL: ' . $e->getMessage());
        }

        // Si DeepL falla, no hay traducción disponible (null, nunca se cachea);
        // el llamador se encarga de mostrar el contenido original.
        return array_fill(0, count($textos), null);
    }
}
