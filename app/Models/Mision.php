<?php
namespace App\Models;
use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;
class Mision extends Model {
    use Translatable;
    protected $table = 'misiones';
    protected $guarded = [];
}
