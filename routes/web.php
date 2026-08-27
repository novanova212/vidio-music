<?php

use Illuminate\Support\Facades\Route;

// Route web biasa (bukan API). Dipakai sekadar untuk cek server hidup.
Route::get('/', function () {
    return response()->json([
        'app' => config('app.name'),
        'status' => 'ok',
    ]);
});