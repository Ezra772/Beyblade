<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blades', function (Blueprint $table) {
            $table->foreignId('series_id')->nullable()->after('series')->constrained('series');
        });

        // Pindahkan data: untuk tiap nilai unik di kolom `series` lama,
        // buat/temukan Series yang sesuai, lalu set series_id di semua Blade terkait.
        $bladeSeriesNames = DB::table('blades')->distinct()->pluck('series');

        foreach ($bladeSeriesNames as $seriesName) {
            if (blank($seriesName)) {
                continue;
            }

            $seriesId = DB::table('series')->where('name', $seriesName)->value('id');

            if (! $seriesId) {
                $seriesId = DB::table('series')->insertGetId([
                    'name' => $seriesName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('blades')->where('series', $seriesName)->update(['series_id' => $seriesId]);
        }
    }

    public function down(): void
    {
        Schema::table('blades', function (Blueprint $table) {
            //
        });
    }
};
