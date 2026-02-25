<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AbsenController extends Controller {
    public function simpan(Request $request) {
        $nama = $request->nama;
        $wilayah = $request->wilayah;
        $lat = $request->lat;
        $lng = $request->lng;
        
        // Simpan data menggunakan Query Builder
        DB::table('data_absens')->insert([
            'nama' => $nama,
            'waktu_absen' => Carbon::now(),
            'wilayah_tugas' => $wilayah,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            // Konversi lat lng menjadi format GEOM PostGIS
            'geom' => DB::raw("ST_GeomFromText('POINT($lng $lat)', 4326)")
        ]);

        return response()->json(['message' => 'Absen berhasil disimpan!']);
    }
}
