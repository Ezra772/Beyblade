<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('beyblades', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('blade_id')->constrained('blades')->restrictOnDelete();
            $table->foreignId('ratchet_id')->constrained('ratchets')->restrictOnDelete();
            $table->foreignId('bit_id')->constrained('bits')->restrictOnDelete();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beyblades');
    }
};
