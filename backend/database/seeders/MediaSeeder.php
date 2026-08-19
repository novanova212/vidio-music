<?php

namespace Database\Seeders;

use App\Models\Song;
use App\Models\Video;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeder contoh. Jalankan: php artisan db:seed --class=MediaSeeder
 * Catatan: seeder ini hanya membuat baris data contoh (butuh file
 * asli di storage/app/public/videos & storage/app/public/songs
 * agar stream/download benar-benar berfungsi).
 */
class MediaSeeder extends Seeder
{
    public function run(): void
    {
        Video::create([
            'title' => 'Contoh Video',
            'slug' => 'contoh-video-'.Str::random(6),
            'description' => 'Video contoh untuk pengujian pemutaran & download.',
            'file_path' => 'videos/contoh.mp4',
            'mime_type' => 'video/mp4',
            'file_size' => 0,
        ]);

        Song::create([
            'title' => 'Contoh Lagu',
            'slug' => 'contoh-lagu-'.Str::random(6),
            'artist' => 'Artis Contoh',
            'file_path' => 'songs/contoh.mp3',
            'mime_type' => 'audio/mpeg',
            'file_size' => 0,
        ]);
    }
}