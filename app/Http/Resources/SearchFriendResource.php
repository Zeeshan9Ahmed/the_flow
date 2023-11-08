<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SearchFriendResource extends JsonResource
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
            'id' => $this->id,
            'full_name' => $this->full_name,
            'avatar' => $this->avatar??"",
            'friend_ship_status' => $this->getFriendShipStatus(auth()->user()),
            'is_following'=> $this->is_following,
            'following_count' => $this->following_count

        ];
    }
}
