<?php

namespace App\Models;

use App\Traits\Friendable;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable,Friendable,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'address',
        'zip_code',
        'state',
        'avatar',
        'password',
        'device_type',
        'device_token',
        'is_forgot',
        'is_verified',
        'verification_code',
        'is_blocked',
        'date_of_birth'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'reciever_id');
    }


    public function following() {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id');
    }
    
    // users that follow this user
    public function followers() {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id');
    }

    public function posts():hasMany
    {
        return $this->hasMany(Post::class,'user_id','id');
    }


    public function events():hasMany
    {
        return $this->hasMany(Event::class,'user_id','id');
    }

    // public function getDateOfBirthAttribute($value)
    // {
    //     return Carbon::parse($value)->age;
    // }

    // public function top_friends():hasMany
    // {
    //     return $this->hasMany(TopList::class,'user_id','id')->where('type','friend');//->select('id','user_id','other_user_id');
    // }

    public function top_friends() 
    {
        return $this->belongsToMany(User::class, 'top_lists', 'user_id', 'other_user_id')->where('type','friend');
    }

    public function top_followers() 
    {
        return $this->belongsToMany(User::class, 'top_lists', 'user_id', 'other_user_id')->where('type','follower');
    }
    // public function top_followers():hasMany
    // {
    //     return $this->hasMany(TopList::class,'user_id','id')->where('type','follower')->select();
    // }

    public function is_follower()
    {
        return $this->hasOne(Follow::class,'following_id','id');
    }

    public function is_following()
    {
        return $this->hasOne(Follow::class,'follower_id','id');
    }

    public function user() :BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function logged_in_user()
    {
        return User::select('id','full_name','email','avatar','cover_image','date_of_birth','zip_code','state','address','phone','is_active','is_profile_complete','is_verified','background_profile',
                            DB::raw('(select count(id) from notifications where to_user_id = '.auth()->id().' AND notification_is_read = "0") as notification_count '),
                        )
                        ->withCount('followers','following')
                        ->where('id',auth()->id())
                        ->first();
    }

    public function user_data()
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
