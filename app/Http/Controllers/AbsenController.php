<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AbsenController extends Controller {
    // Menyimpan data absen dari form
    public function simpan(Request $request) {
        DB::table('data_absens')->insert([
            'nama' => $request->nama,
            'waktu_absen' => Carbon::now(),
            'wilayah_tugas' => $request->wilayah,
            'foto' => $request->foto,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'geom' => DB::raw("ST_GeomFromText('POINT({$request->lng} {$request->lat})', 4326)")
        ]);
        return response()->json(['message' => 'Absen berhasil disimpan!']);
    }

    // Mengambil data untuk ditampilkan di peta
    public function getAbsensi() {
    $data = DB::table('data_absens')
        ->select('nama', 'wilayah_tugas', 'foto', 
            DB::raw("to_char(waktu_absen, 'DD-MM-YYYY HH24:MI') as waktu_absen"),
            DB::raw('ST_X(geom) as lng'), 
            DB::raw('ST_Y(geom) as lat'))
        ->get();
    return response()->json($data);
}
}