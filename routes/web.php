<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpatialController;

Route::get('/', function () {
    return view('map_sungai');
});


Route::get('/upload-data', [SpatialController::class, 'index'])->name('spatial.index');
Route::post('/spatial/upload', [SpatialController::class, 'upload'])->name('spatial.upload');