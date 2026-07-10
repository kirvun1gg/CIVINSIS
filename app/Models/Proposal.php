<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    protected $table = 'propuestas';

    protected $fillable = [
        'titulo', 'descripcion', 'contenido', 'categoria_id', 'usuario_id',
        'diseno', 'color_acento', 'icono_extra', 'efecto_categoria', 'destacada',
        'imagen', 'votos', 'vistas', 'estado', 'censurada', 'razon_censura', 'fecha_creacion',
    ];

    protected $casts = [
        'efecto_categoria' => 'boolean',
        'destacada'        => 'boolean',
        'censurada'        => 'boolean',
        'fecha_creacion'   => 'datetime',
    ];

    public function categoria() { return $this->belongsTo(Categoria::class, 'categoria_id'); }
    public function autor()     { return $this->belongsTo(User::class, 'usuario_id'); }
    public function votosRel()  { return $this->hasMany(Voto::class, 'propuesta_id'); }
    public function comentarios(){ return $this->hasMany(Comentario::class, 'propuesta_id'); }
}
