<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratchets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('height', 6, 2);
            $table->decimal('weight', 6, 2);
            $table->decimal('stability', 5, 2);
            $table->decimal('burst_resistance', 5, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratchets');
    }
};
