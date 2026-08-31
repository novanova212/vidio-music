<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaReaction extends Model
{
    protected $fillable = [
        'target_type',
        'target_key',
        'guest_id',
        'reaction',
    ];
}
