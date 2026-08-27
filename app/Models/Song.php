<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'artist',
        'album',
        'source_url', // link ke musik asli (sudah di-hosting di tempat lain)
        'cover_url',  // link gambar cover (opsional)
        'mime_type',
        'plays',
        'downloads',
    ];

    public function getStreamUrlAttribute(): string
    {
        return $this->source_url;
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('api.songs.download', $this->slug);
    }
}