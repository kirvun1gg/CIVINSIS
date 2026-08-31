<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Translatable;

class Debate extends Model
{
    use Translatable;

    protected $table = 'debates';

    protected $fillable = [
        'titulo', 'descripcion', 'categoria_id', 'usuario_id',
        'estado', 'participantes', 'respuestas_count',
        'censurado', 'razon_censura',
        'resumen_ia', 'resumen_generado_at', 'fecha_creacion', 'idioma_original',
    ];

    /** Campos traducibles vía TranslationService (App\Traits\Translatable). */
    protected $translatableFields = ['titulo', 'descripcion'];

    protected $casts = [
        'censurado'           => 'boolean',
        'fecha_creacion'      => 'datetime',
        'resumen_generado_at' => 'datetime',
    ];

    public function categoria()  { return $this->belongsTo(Categoria::class, 'categoria_id'); }
    public function autor()      { return $this->belongsTo(User::class, 'usuario_id'); }
    public function respuestas() { return $this->hasMany(DebateRespuesta::class, 'debate_id'); }
}
