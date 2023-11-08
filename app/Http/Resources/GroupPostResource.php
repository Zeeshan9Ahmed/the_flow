<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GroupPostResource extends JsonResource
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
            'text' => $this->text,
            'file' => $this->file,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'is_like' => $this->is_like,
            'likes_count' => $this->likes_count,
            'comments_count' => $this->comments_count,
            'user' => SearchFriendResource::make($this->author)
        ];
    }
}
