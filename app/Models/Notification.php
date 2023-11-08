<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $fillable = ['id','title','from_user_id','to_user_id','redirection_id','model_type_id','notification_type','notification_is_read','description'];

    public function sender()
    {
        return $this->belongsTo(User::class,'from_user_id');
    }
}
