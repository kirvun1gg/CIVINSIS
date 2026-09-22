<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CiviConversacion extends Model
{
    protected $table = 'civi_conversaciones';

    protected $fillable = ['usuario_id', 'titulo'];

    protected static function boot()
    {
        parent::boot();

        // Este proyecto corre sobre tablas MyISAM (ver comentarios en otras
        // migraciones sobre el error 1824): 'onDelete cascade' del FK no se
        // aplica de verdad a nivel de motor, así que hay que borrar los
        // mensajes a mano al borrar la conversación.
        static::deleting(function (CiviConversacion $conversacion) {
            $conversacion->mensajes()->delete();
        });
    }

    public function mensajes()
    {
        return $this->hasMany(CiviMensaje::class, 'conversacion_id')->orderBy('created_at');
    }

    /** Título corto generado a partir del primer mensaje del usuario. */
    public static function tituloDesde(string $mensaje): string
    {
        $t = trim(preg_replace('/\s+/', ' ', $mensaje));
        return mb_strlen($t) > 60 ? mb_substr($t, 0, 57) . '…' : $t;
    }
}
