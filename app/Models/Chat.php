<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;
    protected $table = 'st_chat';
    protected $fillable = [
        'chat_sender_id',
        'chat_reciever_id',
        'chat_message',
        'chat_type'
    ];
}
