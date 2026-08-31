<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaComment extends Model
{
    protected $fillable = [
        'target_type',
        'target_key',
        'author_name',
        'body',
    ];
}
