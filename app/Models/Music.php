<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Music extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'music_id',
        'music_image_url',
        'music_url',
        'artist_name'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
