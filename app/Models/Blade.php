<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Blade extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'series_id',
        'product_code',
        'weight',
        'attack',
        'defense',
        'stamina',
        'smash',
        'upper',
        'recoil',
        'burst_resistance',
    ];

    public function series(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    protected $casts = [
        'weight' => 'decimal:2',
        'attack' => 'decimal:2',
        'defense' => 'decimal:2',
        'stamina' => 'decimal:2',
        'smash' => 'decimal:2',
        'upper' => 'decimal:2',
        'recoil' => 'decimal:2',
        'burst_resistance' => 'decimal:2',
    ];

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'owner')->where('is_primary', true);
    }
}
