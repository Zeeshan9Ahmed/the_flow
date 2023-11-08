<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'user_id' => $this->user_id,
            'title' => $this->title,
            'image' => $this->image,
            'description' => $this->description,
            'time' => $this->time, 
            'date' => $this->date,
            'address' => $this->address,
            'zip_code' => $this->zip_code,
            'state' => $this->state,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at, 
            'user' => new SearchFriendResource($this->user),
        ];
        return parent::toArray($request);
    }
}
