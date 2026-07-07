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
        Schema::table('blades', function (Blueprint $table) {
            $table->dropColumn('series');
            $table->foreignId('series_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('blades', function (Blueprint $table) {
            $table->string('series')->nullable();
            $table->foreignId('series_id')->nullable()->change();
        });
    }
};
