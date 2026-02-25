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
            
            // PERUBAHAN DISINI: Gunakan 'text' untuk menampung data foto Base64 yang panjang
            $table->text('foto')->nullable(); 
            
            $table->timestamps();
        });

        // Menambahkan kolom spasial 'geom' untuk PostGIS
        DB::statement('ALTER TABLE data_absens ADD COLUMN geom GEOMETRY(Point, 4326)');
    }

    public function down(): void {
        Schema::dropIfExists('data_absens');
    }
};