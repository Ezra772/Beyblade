<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beyblade extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'blade_id', 'ratchet_id', 'bit_id'];

    public function blade(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Blade::class);
    }

    public function ratchet(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Ratchet::class);
    }

    public function bit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Bit::class);
    }
}
