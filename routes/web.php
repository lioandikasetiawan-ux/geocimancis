<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpatialController;
use App\Http\Controllers\LokasiKritisController;

Route::get('/', function () {
    return view('map_sungai');
});

Route::get('/upload-data', [SpatialController::class, 'index'])->name('spatial.index');
Route::post('/spatial/upload', [SpatialController::class, 'upload'])->name('spatial.upload');

// Route untuk Lokasi Kritis dari MySQL
Route::get('/api/lokasi-kritis', [LokasiKritisController::class, 'getGeoJson']);
Route::get('/api/lokasi-kritis-mysql', [LokasiKritisController::class, 'getGeoJson']); // <-- TAMBAHKAN INI
Route::get('/api/lokasi-kritis-mysql/{id}', [LokasiKritisController::class, 'getById']);
Route::get('/api/lokasi-kritis-mysql/filter', [LokasiKritisController::class, 'getFiltered']);