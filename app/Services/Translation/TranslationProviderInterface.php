<?php

namespace App\Services\Translation;

interface TranslationProviderInterface
{
    /**
     * Devuelve la traducción, o null si el proveedor no pudo traducir
     * (sin key configurada, error de red, respuesta inválida, etc). null es
     * una señal explícita de "no disponible": el llamador NUNCA debe
     * cachear un null como si fuera una traducción real, para que una vez
     * que el proveedor esté disponible se intente traducir de nuevo.
     */
    public function translate(string $text, string $targetLocale, ?string $sourceLocale = null): ?string;

    /**
     * Traduce varios textos en una sola llamada al proveedor.
     * Devuelve un array en el MISMO orden que $texts, con null en las
     * posiciones que no se pudieron traducir (ver translate()).
     *
     * @param string[] $texts
     * @return (string|null)[]
     */
    public function translateMany(array $texts, string $targetLocale, ?string $sourceLocale = null): array;
}
