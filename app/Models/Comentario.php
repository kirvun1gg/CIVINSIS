<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Translatable;

class Comentario extends Model
{
    use Translatable;

    protected $table = 'comentarios';
    protected $fillable = [
        'propuesta_id', 'usuario_id', 'contenido',
        'contenido_original', 'censurado', 'razon_censura', 'fecha_creacion', 'idioma_original',
    ];

    /** Campos traducibles vía TranslationService (App\Traits\Translatable). */
    protected $translatableFields = ['contenido'];
    protected $casts = [
        'fecha_creacion' => 'datetime',
        'censurado'      => 'boolean',
    ];

    public function propuesta() { return $this->belongsTo(Proposal::class, 'propuesta_id'); }
    public function usuario()   { return $this->belongsTo(User::class, 'usuario_id'); }
}
