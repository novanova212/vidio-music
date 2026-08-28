<?php

use App\Http\Controllers\Api\DiscoverController;
use App\Http\Controllers\Api\MusicController;
use App\Http\Controllers\Api\VideoController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes - Vidio Music
|--------------------------------------------------------------------------
| Catatan: tidak ada lagi endpoint /stream. Video & musik diputar
| LANGSUNG dari source_url (link ke sumber asli), tidak lewat server
| kita, jadi lebih ringan. Endpoint /download tetap lewat backend
| supaya jumlah unduhan bisa dihitung sebelum diarahkan ke sumbernya.
|
| Route 'discover/*' beda dari yang lain: itu BUKAN data dari database
| kita, tapi diambil otomatis dari YouTube Data API tiap dipanggil
| (di-cache 1 jam). Dipakai supaya beranda selalu ada isi walau belum
| ada satupun video/musik yang ditambahkan manual.
*/

Route::prefix('discover')->name('api.discover.')->group(function () {
    Route::get('/videos', [DiscoverController::class, 'videos'])->name('videos');
    Route::get('/music', [DiscoverController::class, 'music'])->name('music');
});

Route::prefix('videos')->name('api.videos.')->group(function () {
    Route::get('/', [VideoController::class, 'index'])->name('index');
    Route::post('/', [VideoController::class, 'store'])->name('store');
    Route::get('/{slug}', [VideoController::class, 'show'])->name('show');
    Route::get('/{slug}/download', [VideoController::class, 'download'])->name('download');
    Route::delete('/{slug}', [VideoController::class, 'destroy'])->name('destroy');
});

Route::prefix('songs')->name('api.songs.')->group(function () {
    Route::get('/', [MusicController::class, 'index'])->name('index');
    Route::post('/', [MusicController::class, 'store'])->name('store');
    Route::get('/{slug}', [MusicController::class, 'show'])->name('show');
    Route::get('/{slug}/download', [MusicController::class, 'download'])->name('download');
    Route::delete('/{slug}', [MusicController::class, 'destroy'])->name('destroy');
});

Route::get('/youtube/search', [DiscoverController::class, 'search']);