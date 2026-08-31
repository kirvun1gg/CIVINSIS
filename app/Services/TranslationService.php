<?php

namespace App\Services;

use App\Models\Translation;
use App\Services\Translation\TranslationProviderInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Punto único de acceso a traducción de contenido dinámico. Nada más en la
 * app debe llamar directamente a un TranslationProviderInterface: todo pasa
 * por aquí, que se encarga de cachear en BD, invalidar por cambios de
 * contenido (comparando content_hash) y de no romper la página si el
 * proveedor (DeepL) falla.
 */
class TranslationService
{
    public function __construct(private TranslationProviderInterface $provider)
    {
    }

    /**
     * Devuelve el campo $field de $model traducido a $locale, usando la
     * traducción cacheada si sigue siendo válida o generando una nueva.
     */
    public function translateField(Model $model, string $field, string $locale): string
    {
        $original = (string) ($model->{$field} ?? '');
        if ($original === '') return '';

        $hash = sha1($original);

        $fila = Translation::query()
            ->where('translatable_type', get_class($model))
            ->where('translatable_id', $model->getKey())
            ->where('locale', $locale)
            ->where('field', $field)
            ->first();

        if ($fila && $fila->content_hash === $hash) {
            return $fila->translated_text;
        }

        try {
            $traducido = $this->provider->translate($original, $locale, $model->idioma_original ?? 'es');
        } catch (\Throwable $e) {
            Log::error('TranslationService: fallo traduciendo campo', ['error' => $e->getMessage()]);
            return $original;
        }

        // El proveedor no pudo traducir (sin key, error, etc): se muestra el
        // original SIN cachear nada, para reintentar la próxima vez que el
        // proveedor esté disponible (nunca se guarda un "null" como si fuera
        // una traducción real).
        if ($traducido === null) return $original;

        Translation::query()->updateOrCreate(
            [
                'translatable_type' => get_class($model),
                'translatable_id'   => $model->getKey(),
                'locale'            => $locale,
                'field'             => $field,
            ],
            [
                'translated_text' => $traducido,
                'content_hash'    => $hash,
            ]
        );

        return $traducido;
    }

    /**
     * Pre-carga en una sola llamada agrupada al proveedor las traducciones
     * que falten para $fields de todos los $models, evitando una llamada
     * HTTP por elemento en listados (N+1). Tras llamar a esto, cada
     * $model->translated($field) de la colección lee de caché.
     *
     * @param iterable<Model> $models
     * @param string[] $fields
     */
    public function warmMany(iterable $models, array $fields, string $locale): void
    {
        $models = collect($models);
        if ($models->isEmpty()) return;

        $tipo = get_class($models->first());
        $ids  = $models->pluck('id')->all();

        $existentes = Translation::query()
            ->where('translatable_type', $tipo)
            ->whereIn('translatable_id', $ids)
            ->where('locale', $locale)
            ->whereIn('field', $fields)
            ->get()
            ->keyBy(fn ($t) => $t->translatable_id . '|' . $t->field);

        // Reunir, en una sola tanda, los textos que faltan o quedaron obsoletos.
        $pendientes = []; // ['modelo' => Model, 'field' => string, 'texto' => string, 'hash' => string]
        foreach ($models as $model) {
            foreach ($fields as $field) {
                $original = (string) ($model->{$field} ?? '');
                if ($original === '') continue;
                $hash  = sha1($original);
                $clave = $model->getKey() . '|' . $field;
                $fila  = $existentes->get($clave);
                if ($fila && $fila->content_hash === $hash) continue;

                $pendientes[] = ['modelo' => $model, 'field' => $field, 'texto' => $original, 'hash' => $hash];
            }
        }

        if (empty($pendientes)) return;

        try {
            $traducciones = $this->provider->translateMany(
                array_column($pendientes, 'texto'),
                $locale
            );
        } catch (\Throwable $e) {
            Log::error('TranslationService: fallo en traducción por lote', ['error' => $e->getMessage()]);
            return;
        }

        foreach ($pendientes as $i => $p) {
            $traducido = $traducciones[$i] ?? null;
            // Sin traducción disponible para este texto: no se cachea nada
            // (ver nota en translateField sobre por qué nunca se guarda un null).
            if ($traducido === null) continue;

            Translation::query()->updateOrCreate(
                [
                    'translatable_type' => $tipo,
                    'translatable_id'   => $p['modelo']->getKey(),
                    'locale'            => $locale,
                    'field'             => $p['field'],
                ],
                [
                    'translated_text' => $traducido,
                    'content_hash'    => $p['hash'],
                ]
            );
        }
    }
}
