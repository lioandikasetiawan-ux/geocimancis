<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AbsenController;


Route::get('/', function () {
    return view('map_sungai');
});

Route::post('/simpan-absen', [AbsenController::class, 'simpan']);