<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Desafio extends Model
{
    protected $table = 'desafios';

    protected $fillable = [
        'titulo', 'descripcion', 'dificultad', 'categoria_id', 'icono',
        'xp_recompensa', 'reputacion_recompensa', 'insignia_id',
        'activo', 'orden',
    ];

    protected $casts = ['activo' => 'boolean'];

    public function categoria() { return $this->belongsTo(Categoria::class, 'categoria_id'); }
    public function insignia()  { return $this->belongsTo(Insignia::class, 'insignia_id'); }
    public function progresos() { return $this->hasMany(UsuarioDesafio::class, 'desafio_id'); }
}
