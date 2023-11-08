<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Group extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'user_id',
        'is_private',
        'image'
    ];

    public function posts() :HasMany
    {
        return $this->hasMany(Post::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->select(
            'id','full_name','avatar',
            DB::raw('(select count(id) from follows where follower_id = "'.auth()->id().'" AND  following_id = users.id) as is_following'),
            DB::raw('(select count(id) from follows where follower_id  = users.id) as following_count'),
            DB::raw('(select count(id) from friend_requests where (sender_id  = users.id OR recipient_id = users.id) AND status = 1 ) as is_friend'),
            DB::raw('(select count(id) from friend_requests where (sender_id  = users.id  AND recipient_id = "'.auth()->id().'" ) AND status = 0 ) as  has_friend_request'),
        );
    }
}
