<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Ratchet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'height',
        'weight',
        'stability',
        'burst_resistance',
    ];

    protected $casts = [
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
        'stability' => 'decimal:2',
        'burst_resistance' => 'decimal:2',
    ];

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'owner')->where('is_primary', true);
    }
}
