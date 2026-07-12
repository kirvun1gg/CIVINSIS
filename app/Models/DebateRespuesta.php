<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebateRespuesta extends Model
{
    protected $table = 'debate_respuestas';

    protected $fillable = [
        'debate_id', 'usuario_id', 'parent_id', 'cita_id',
        'contenido', 'contenido_original', 'censurado', 'razon_censura',
        'votos', 'destacada', 'fecha_creacion',
    ];

    protected $casts = [
        'censurado'      => 'boolean',
        'destacada'      => 'boolean',
        'fecha_creacion' => 'datetime',
    ];

    public function debate()   { return $this->belongsTo(Debate::class, 'debate_id'); }
    public function usuario()  { return $this->belongsTo(User::class, 'usuario_id'); }
    public function parent()   { return $this->belongsTo(DebateRespuesta::class, 'parent_id'); }
    public function cita()     { return $this->belongsTo(DebateRespuesta::class, 'cita_id'); }
    public function hijos()    { return $this->hasMany(DebateRespuesta::class, 'parent_id'); }
    public function votosRel() { return $this->hasMany(DebateVotoRespuesta::class, 'respuesta_id'); }
}
