<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categorias';
    protected $fillable = ['nombre', 'icono', 'color', 'descripcion', 'efecto'];

    public function propuestas() { return $this->hasMany(Proposal::class, 'categoria_id'); }
}
