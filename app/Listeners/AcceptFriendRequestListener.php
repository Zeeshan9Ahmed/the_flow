<?php

namespace App\Listeners;

use App\Events\AcceptFriendRequestEvent;
use App\Http\Resources\FreindShipStatusResource;
use App\Http\Resources\SearchFriendResource;
use App\Models\User;
use App\Services\Notifications\CreateDBNotification;
use App\Services\Notifications\PushNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AcceptFriendRequestListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public $notification;
    public function __construct( PushNotificationService $notification )
    {
        $this->notification = $notification;
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\AcceptFriendRequestEvent  $event
     * @return void
     */
    public function handle(AcceptFriendRequestEvent $event)
    {
        
        $data = [
            'to_user_id'        =>  $event->recepient->id,
            'from_user_id'      =>  $event->sender->id,
            'notification_type' =>  'FRIEND_REQUEST_ACCEPTED',
            'title'             =>  $event->sender->full_name ." accepted your  friend request",
            'redirection_id'              =>   $event->sender->id
        ];

        $save_notification = app(CreateDBNotification::class)->execute($data);
        $send_push = app(PushNotificationService::class)->execute($data,[$event->recepient->device_token]);

    }
}
