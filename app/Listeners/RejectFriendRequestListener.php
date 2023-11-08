<?php

namespace App\Listeners;

use App\Events\RejectFriendRequestEvent;
use App\Services\Notifications\CreateDBNotification;
use App\Services\Notifications\PushNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RejectFriendRequestListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\RejectFriendRequestEvent  $event
     * @return void
     */
    public function handle(RejectFriendRequestEvent $event)
    {
        $data = [
            'to_user_id'        =>  $event->recepient->id,
            'from_user_id'      =>  $event->sender->id,
            'notification_type' =>  'FRIEND_REQUEST_REJECTED',
            'title'             =>  $event->sender->full_name ." rejected your  friend request",
            'redirection_id'              =>   $event->sender->id
        ];

        $save_notification = app(CreateDBNotification::class)->execute($data);
        $send_push = app(PushNotificationService::class)->execute($data,[$event->recepient->device_token]);
    }
}
