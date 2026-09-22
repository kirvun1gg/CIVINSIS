<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = ['usuario_id', 'tipo', 'clave', 'datos', 'icono', 'color', 'mensaje', 'enlace', 'leida'];

    protected $casts = ['leida' => 'boolean', 'datos' => 'array'];

    public function usuario() { return $this->belongsTo(User::class, 'usuario_id'); }

    /**
     * Atajo para crear una notificación desde cualquier controlador/servicio.
     *
     * No guarda el texto ya traducido: guarda una $clave (sufijo de
     * civinsis.notificaciones.*) + $datos (parámetros del placeholder), para
     * que se traduzca al idioma del DESTINATARIO en el momento de leerla
     * (ver mensajeTraducido()) y no en el idioma de quien disparó la acción.
     */
    public static function crear(int $usuarioId, string $tipo, string $clave, array $datos = [], ?string $enlace = null, string $icono = 'fas fa-bell', string $color = '#36c0a1'): self
    {
        return self::create([
            'usuario_id' => $usuarioId,
            'tipo'       => $tipo,
            'icono'      => $icono,
            'color'      => $color,
            'clave'      => $clave,
            'datos'      => $datos,
            'enlace'     => $enlace,
            'leida'      => false,
        ]);
    }

    /**
     * Mensaje ya traducido al idioma actual (App::getLocale()). Las
     * notificaciones antiguas (creadas antes de este cambio) no tienen
     * 'clave' y conservan su texto original tal cual se guardó.
     */
    public function mensajeTraducido(): string
    {
        if (!$this->clave) {
            return $this->mensaje ?? '';
        }

        $datos = $this->datos ?? [];

        // La fase de una propuesta se guarda como clave (no como texto ya
        // traducido) para poder resolver su etiqueta en el idioma de quien
        // lee la notificación, no de quien disparó el cambio de fase.
        if (isset($datos['fase_clave'])) {
            $datos['fase'] = __('civinsis.progreso.' . $datos['fase_clave'] . '_label');
            unset($datos['fase_clave']);
        }

        return __('civinsis.notificaciones.' . $this->clave, $datos);
    }
}
