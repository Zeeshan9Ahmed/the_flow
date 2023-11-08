<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
            'from_user_id' => $this->from_user_id,
            'channel_name' => $this->channel_name,
            'channel_token' => $this->channel_token,
            'title'=>$this->title??"",
            'notification_type'=>$this->notification_type??"",
            'redirection_id'=>$this->redirection_id,
            'date'=>$this->created_at->diffForHumans(),
        ];
    }
}
