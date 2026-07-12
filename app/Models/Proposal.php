<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    protected $table = 'propuestas';

    protected $fillable = [
        'titulo', 'descripcion', 'contenido', 'categoria_id', 'usuario_id', 'desafio_id',
        'diseno', 'color_acento', 'icono_extra', 'efecto_categoria', 'destacada',
        'imagen', 'votos', 'vistas', 'estado', 'censurada', 'razon_censura', 'fecha_creacion',
        'progreso', 'progreso_actualizado_at', 'progreso_visto',
    ];

    protected $casts = [
        'efecto_categoria' => 'boolean',
        'destacada'        => 'boolean',
        'censurada'        => 'boolean',
        'fecha_creacion'   => 'datetime',
        'progreso_actualizado_at' => 'datetime',
        'progreso_visto'   => 'boolean',
    ];

    public function categoria() { return $this->belongsTo(Categoria::class, 'categoria_id'); }
    public function desafio()   { return $this->belongsTo(Desafio::class, 'desafio_id'); }
    public function autor()     { return $this->belongsTo(User::class, 'usuario_id'); }
    public function votosRel()  { return $this->hasMany(Voto::class, 'propuesta_id'); }
    public function comentarios(){ return $this->hasMany(Comentario::class, 'propuesta_id'); }
    public function progresoHistorial() { return $this->hasMany(PropuestaProgreso::class, 'propuesta_id')->orderBy('fecha'); }
}
