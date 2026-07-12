<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropuestaProgreso extends Model
{
    protected $table = 'propuesta_progreso_historial';

    protected $fillable = ['propuesta_id', 'progreso', 'usuario_id', 'fecha'];

    protected $casts = ['fecha' => 'datetime'];

    public function propuesta() { return $this->belongsTo(Proposal::class, 'propuesta_id'); }
    public function usuario()   { return $this->belongsTo(User::class, 'usuario_id'); }
}
