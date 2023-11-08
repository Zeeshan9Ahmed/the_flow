<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Event extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'time',
        'date',
        'address',
        'zip_code',
        'state',
        'user_id',
        'image'
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->select(
            'id','full_name','avatar',
            DB::raw('(select count(id) from follows where follower_id = "'.auth()->id().'" AND  following_id = users.id) as is_following'),
            DB::raw('(select count(id) from follows where follower_id  = users.id) as following_count'),
        );
        return $this->hasOne(User::class,'id', 'user_id');
    }


    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans();
    }
}
