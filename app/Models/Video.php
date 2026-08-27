<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'source_url',   // link ke video asli (sudah di-hosting di tempat lain)
        'thumbnail_url', // link gambar thumbnail (opsional, juga dari luar)
        'mime_type',
        'views',
        'downloads',
    ];

    // Dipakai frontend untuk tag <video src="...">. Karena file-nya
    // memang tidak disimpan di server ini, langsung arahkan ke source_url.
    public function getStreamUrlAttribute(): string
    {
        return $this->source_url;
    }

    // Tetap lewat backend (bukan langsung ke source_url) supaya jumlah
    // unduhan bisa dihitung, baru diarahkan (redirect) ke sumber aslinya.
    public function getDownloadUrlAttribute(): string
    {
        return route('api.videos.download', $this->slug);
    }
}