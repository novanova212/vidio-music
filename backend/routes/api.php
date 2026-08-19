<?php

use App\Http\Controllers\Api\MusicController;
use App\Http\Controllers\Api\VideoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Vidio Music
|--------------------------------------------------------------------------
| Semua route di sini otomatis diberi prefix /api oleh Laravel
| (lihat bootstrap/app.php -> ->withRouting(api: ...)).
*/

Route::prefix('videos')->name('api.videos.')->group(function () {
    Route::get('/', [VideoController::class, 'index'])->name('index');
    Route::post('/', [VideoController::class, 'store'])->name('store');
    Route::get('/{slug}', [VideoController::class, 'show'])->name('show');
    Route::get('/{slug}/stream', [VideoController::class, 'stream'])->name('stream');
    Route::get('/{slug}/download', [VideoController::class, 'download'])->name('download');
});

Route::prefix('songs')->name('api.songs.')->group(function () {
    Route::get('/', [MusicController::class, 'index'])->name('index');
    Route::post('/', [MusicController::class, 'store'])->name('store');
    Route::get('/{slug}', [MusicController::class, 'show'])->name('show');
    Route::get('/{slug}/stream', [MusicController::class, 'stream'])->name('stream');
    Route::get('/{slug}/download', [MusicController::class, 'download'])->name('download');
});