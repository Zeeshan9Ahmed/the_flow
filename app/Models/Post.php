<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Post extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'group_id',
        'text',
        'file',
        'type'

    ];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans();
    }

    public function likes():hasMany
    {
        return $this->hasMany(Like::class,'post_id','id');
    }

    
    public function comments():hasMany
    {
        return $this->hasMany(Comment::class,'post_id','id');
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }
    
    public function top_friends():hasMany
    {
        return $this->hasMany(TopList::class,'user_id','id')->where('type','friend')->select('id','user_id','other_user_id');
    }

    public function top_followers():hasMany
    {
        return $this->hasMany(TopList::class,'user_id','id')->where('type','follower')->select('id','user_id','other_user_id');
    }

    public function group_name() 
    {
        return $this->hasOne(Group::class,'id','group_id')->select('id','name','image');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->select(
            'id','full_name','avatar',
            DB::raw('(select count(id) from follows where follower_id = "'.auth()->id().'" AND  following_id = users.id) as is_following'),
            DB::raw('(select count(id) from follows where follower_id  = users.id) as following_count'),
        );
    }
}
