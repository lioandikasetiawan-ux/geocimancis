<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AbsenController;

Route::get('/', function () {
    return view('map_sungai');
});

// Route untuk menampilkan halaman absensi
Route::get('/absensi', function () {
    return view('absensi');
});

// Route POST untuk menyimpan data
Route::post('/simpan-absen', [AbsenController::class, 'simpan']);
Route::get('/get-absensi', [AbsenController::class, 'getAbsensi']);