<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactoMensaje extends Model
{
    protected $table = 'contacto_mensajes';
    protected $fillable = ['nombre', 'email', 'asunto', 'mensaje', 'usuario_id', 'leido', 'respuesta', 'fecha_creacion'];
    protected $casts = ['leido' => 'boolean', 'fecha_creacion' => 'datetime'];
}
