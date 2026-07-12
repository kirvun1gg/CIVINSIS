<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = ['usuario_id', 'tipo', 'icono', 'color', 'mensaje', 'enlace', 'leida'];

    protected $casts = ['leida' => 'boolean'];

    public function usuario() { return $this->belongsTo(User::class, 'usuario_id'); }

    /** Atajo para crear una notificación desde cualquier controlador/servicio. */
    public static function crear(int $usuarioId, string $tipo, string $mensaje, ?string $enlace = null, string $icono = 'fas fa-bell', string $color = '#36c0a1'): self
    {
        return self::create([
            'usuario_id' => $usuarioId,
            'tipo'       => $tipo,
            'icono'      => $icono,
            'color'      => $color,
            'mensaje'    => $mensaje,
            'enlace'     => $enlace,
            'leida'      => false,
        ]);
    }
}
