<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CiviMensaje extends Model
{
    protected $table = 'civi_mensajes';

    protected $fillable = ['conversacion_id', 'rol', 'contenido'];

    public function conversacion()
    {
        return $this->belongsTo(CiviConversacion::class, 'conversacion_id');
    }
}
