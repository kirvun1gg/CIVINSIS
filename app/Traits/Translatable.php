<?php

namespace App\Traits;

use App\Models\Translation;
use App\Services\TranslationService;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;

trait Translatable
{
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    /**
     * Devuelve $field en el idioma actual (o el indicado). Si el idioma
     * coincide con el original del contenido, devuelve el atributo tal
     * cual; si no, busca/genera la traducción vía TranslationService.
     */
    public function translated(string $field, ?string $locale = null): string
    {
        $locale = $locale ?? App::getLocale();
        $original = (string) ($this->{$field} ?? '');

        if ($locale === ($this->idioma_original ?? 'es')) {
            return $original;
        }

        return app(TranslationService::class)->translateField($this, $field, $locale);
    }

    public function idiomasTraducibles(): array
    {
        return $this->translatableFields ?? [];
    }
}
