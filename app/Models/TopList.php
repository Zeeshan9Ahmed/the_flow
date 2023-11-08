<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopList extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'other_user_id',
        'type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'other_user_id');
    }
}
