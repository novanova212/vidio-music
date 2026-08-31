<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaStat extends Model
{
    protected $fillable = [
        'target_type',
        'target_key',
        'views',
        'likes',
        'dislikes',
    ];
}
