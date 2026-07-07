<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blades', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('series');
            $table->string('product_code')->nullable();
            $table->decimal('weight', 6, 2);
            $table->decimal('attack', 5, 2);
            $table->decimal('defense', 5, 2);
            $table->decimal('stamina', 5, 2);
            $table->decimal('smash', 5, 2);
            $table->decimal('upper', 5, 2);
            $table->decimal('recoil', 5, 2);
            $table->decimal('burst_resistance', 5, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blades');
    }
};
