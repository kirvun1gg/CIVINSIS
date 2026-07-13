<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voto extends Model
{
    protected $table = 'votos';
    protected $fillable = ['propuesta_id', 'usuario_id', 'aspecto'];

    public function propuesta() { return $this->belongsTo(Proposal::class, 'propuesta_id'); }
    public function usuario()   { return $this->belongsTo(User::class, 'usuario_id'); }
}
