<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    public function userlevel(){
        return $this->hasOne(UserLevel::class, 'level_id', 'id');
    }

    public function questions(){
        return $this->hasMany(Question::class, 'level_id', 'id');
    }
}
