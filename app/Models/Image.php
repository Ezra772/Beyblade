<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    protected $fillable = ['owner_id', 'owner_type', 'path', 'is_primary'];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
