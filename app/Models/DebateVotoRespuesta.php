<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebateVotoRespuesta extends Model
{
    protected $table = 'debate_votos_respuesta';
    protected $fillable = ['respuesta_id', 'usuario_id'];

    public function respuesta() { return $this->belongsTo(DebateRespuesta::class, 'respuesta_id'); }
    public function usuario()   { return $this->belongsTo(User::class, 'usuario_id'); }
}
