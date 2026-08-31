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
        'source_url',
        'thumbnail_url',
        'mime_type',
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
        return route('api.videos.download', $this->slug);
    }
}
