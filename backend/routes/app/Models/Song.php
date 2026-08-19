<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Song extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'artist',
        'album',
        'file_path',
        'mime_type',
        'file_size',
        'duration',
        'cover_path',
        'plays',
        'downloads',
    ];

    // URL streaming (dipakai <audio> tag di frontend)
    public function getStreamUrlAttribute(): string
    {
        return route('api.songs.stream', $this->slug);
    }

    // URL download langsung (file asli, attachment)
    public function getDownloadUrlAttribute(): string
    {
        return route('api.songs.download', $this->slug);
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null;
    }
}