<?php

namespace App\Listeners;

use App\Events\FollowUserEvent;
use App\Models\Notification;
use App\Services\Notifications\CreateDBNotification;
use App\Services\Notifications\PushNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class FollowUserListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\FollowUserEvent  $event
     * @return void
     */
    public function handle(FollowUserEvent $event)
    {
        $data = [
            'to_user_id'        =>  $event->recipient->id,
            'from_user_id'      =>  auth()->id(),
            'notification_type' =>  'FOLLOWED',
            'title' => auth()->user()->full_name ." has followed you ",
            // 'data' => ['user_id' => auth()->id() , 'full_name' => auth()->user()->full_name , 'avatar' => auth()->user()->avatar],
        ];
        
        
        $notification = Notification::where($data)->first();
        if( $notification )
        {
            $notification->delete();
        }
        $data['redirection_id'] =  auth()->id() ;
        
        $save_notification = app(CreateDBNotification::class)->execute($data);
        $send_push = app(PushNotificationService::class)->execute($data,[$event->recipient->device_token]);
    }
}
