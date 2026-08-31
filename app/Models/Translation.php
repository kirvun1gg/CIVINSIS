<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Translation extends Model
{
    protected $fillable = [
        'translatable_type',
        'translatable_id',
        'locale',
        'field',
        'translated_text',
        'content_hash',
    ];

    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }
}
