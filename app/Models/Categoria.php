<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use Translatable;

    protected $table = 'categorias';
    protected $fillable = ['nombre', 'icono', 'color', 'descripcion', 'efecto'];
    protected $translatableFields = ['nombre', 'descripcion'];

    public function propuestas() { return $this->hasMany(Proposal::class, 'categoria_id'); }
}
