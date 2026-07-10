<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModeracionAlerta extends Model
{
    protected $table = 'moderacion_alertas';

    protected $fillable = [
        'tipo',
        'referencia_id',
        'contenido_original',
        'razon',
        'severidad',
        'revisado',
        'revisado_at',
        'revisado_por',
    ];

    protected $casts = [
        'revisado'    => 'boolean',
        'revisado_at' => 'datetime',
    ];
}
