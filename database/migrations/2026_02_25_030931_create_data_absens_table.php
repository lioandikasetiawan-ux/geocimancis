<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('data_absens', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->dateTime('waktu_absen');
            $table->string('wilayah_tugas');
            $table->string('foto')->nullable(); // Simpan path gambar
            $table->timestamps();
        });

        // Menambahkan kolom spasial 'geom' untuk titik (Point)
        // Kita gunakan DB::statement karena Laravel Blueprint standar tidak selalu mendukung tipe geometri PostGIS secara native
        DB::statement('ALTER TABLE data_absens ADD COLUMN geom GEOMETRY(Point, 4326)');
    }

    public function down(): void {
        Schema::dropIfExists('data_absens');
    }
};