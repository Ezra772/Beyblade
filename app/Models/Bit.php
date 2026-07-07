<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Bit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'speed',
        'stamina',
        'grip',
        'control',
        'movement',
        'dash',
    ];

    protected $casts = [
        'speed' => 'decimal:2',
        'stamina' => 'decimal:2',
        'grip' => 'decimal:2',
        'control' => 'decimal:2',
        'movement' => 'decimal:2',
        'dash' => 'decimal:2',
    ];

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'owner')->where('is_primary', true);
    }
}
