<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use phpDocumentor\Reflection\Types\Null_;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'full_name' =>$this->full_name??"",
            'email' =>$this->email??"",
            'age' => $this->date_of_birth??"",
            'phone' =>$this->phone??"",
            'address' =>$this->address??"",
            'avatar'=>$this->avatar?asset($this->avatar):"",
            'cover_image' => $this->cover_image?asset($this->cover_image):"",
            'background_profile' => $this->background_profile?asset($this->background_profile):"",
            'is_verified'=>$this->is_verified,
            'profile_completed'=>$this->is_profile_complete,
            'state' => $this->state??"",
            'zip_code' => $this->zip_code??"",
            'followers_count' => $this->followers_count,
            'following_count' => $this->following_count,
            'notification_count' => $this->notification_count,
        ];
    }
}
