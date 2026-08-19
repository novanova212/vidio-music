<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'file_path',
        'mime_type',
        'file_size',
        'duration',
        'thumbnail_path',
        'views',
        'downloads',
    ];

    // URL streaming (dipakai <video> tag di frontend)
    public function getStreamUrlAttribute(): string
    {
        return route('api.videos.stream', $this->slug);
    }

    // URL download langsung (file asli, attachment)
    public function getDownloadUrlAttribute(): string
    {
        return route('api.videos.download', $this->slug);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path ? Storage::disk('public')->url($this->thumbnail_path) : null;
    }
}