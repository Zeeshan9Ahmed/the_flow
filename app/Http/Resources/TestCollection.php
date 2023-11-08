<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TestCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'full_name' =>$this->full_name??"",
            // 'email' =>$this->email??"",
            // 'age' => $this->date_of_birth??"",
            // 'phone' =>$this->phone??"",
            // 'address' =>$this->address??"",
            // 'avatar'=>$this->avatar?asset($this->avatar):"",
            // 'is_verified'=>$this->is_verified,
            // 'profile_completed'=>$this->is_profile_complete,
            // 'state' => $this->state??"",
            // 'zip_code' => $this->zip_code??"",
            // 'followers_count' => $this->followers_count,
            // 'following_count' => $this->following_count,
        ];
        return parent::toArray($request);
    }
}
