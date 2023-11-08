<?php

namespace App\Listeners;

use App\Events\SendFriendRequestEvent;
use App\Models\Notification;
use App\Services\Notifications\CreateDBNotification;
use App\Services\Notifications\PushNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendFriendRequestListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public $notification;
    public function __construct(PushNotificationService $notification )
    {
        $this->notification = $notification;
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\SendFriendRequestEvent  $event
     * @return void
     */
    public function handle(SendFriendRequestEvent $event)
    {
        $data = [
            'to_user_id'        =>  $event->recepient->id,
            'from_user_id'      =>  $event->sender->id,
            'notification_type' =>  'FRIEND_REQUEST',
            'title'             =>  $event->sender->full_name ." sent you a friend request",
            'redirection_id'    => auth()->id()
        ];
        
        $notification = Notification::where($data)->first();
        
        if( $notification )
        {
            $notification->delete();
        }
        $notification = app(CreateDBNotification::class)->execute($data);
        
        $send_push = app(PushNotificationService::class)->execute($data,[$event->recepient->device_token]);
    }
}
