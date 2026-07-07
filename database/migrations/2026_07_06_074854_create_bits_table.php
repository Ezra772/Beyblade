<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bits', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('speed', 5, 2);
            $table->decimal('stamina', 5, 2);
            $table->decimal('grip', 5, 2);
            $table->decimal('control', 5, 2);
            $table->decimal('movement', 5, 2);
            $table->decimal('dash', 5, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bits');
    }
};
