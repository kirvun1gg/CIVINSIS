<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioDesafio extends Model
{
    protected $table = 'usuario_desafios';

    protected $fillable = [
        'usuario_id', 'desafio_id', 'propuesta_id', 'completado', 'completado_at',
    ];

    protected $casts = [
        'completado'    => 'boolean',
        'completado_at' => 'datetime',
    ];

    public function usuario()  { return $this->belongsTo(User::class, 'usuario_id'); }
    public function desafio()  { return $this->belongsTo(Desafio::class, 'desafio_id'); }
    public function propuesta(){ return $this->belongsTo(Proposal::class, 'propuesta_id'); }
}
