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
        'source_url',
        'cover_url',
        'mime_type',
        'plays',
        'views',
        'downloads',
        'likes',
        'dislikes',
    ];

    protected $appends = [
        'stream_url',
        'download_url',
    ];

    protected $casts = [
        'plays' => 'integer',
        'views' => 'integer',
        'downloads' => 'integer',
        'likes' => 'integer',
        'dislikes' => 'integer',
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
