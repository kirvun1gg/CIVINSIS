<?php
namespace App\Models;
use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;
class Logro extends Model {
    use Translatable;
    protected $table = 'logros';
    protected $guarded = [];
}
