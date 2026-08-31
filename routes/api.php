<?php

use App\Http\Controllers\Api\DiscoverController;
use App\Http\Controllers\Api\EngagementController;
use App\Http\Controllers\Api\MusicController;
use App\Http\Controllers\Api\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/youtube/search', [DiscoverController::class, 'search']);

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

Route::get('/me/history', [EngagementController::class, 'history'])->name('api.me.history');

Route::prefix('engage/{type}/{key}')->name('api.engage.')->group(function () {
    Route::get('/', [EngagementController::class, 'show'])->name('show');
    Route::post('/view', [EngagementController::class, 'view'])->name('view');
    Route::post('/react', [EngagementController::class, 'react'])->name('react');
    Route::get('/comments', [EngagementController::class, 'comments'])->name('comments');
    Route::post('/comments', [EngagementController::class, 'storeComment'])->name('comments.store');
});
