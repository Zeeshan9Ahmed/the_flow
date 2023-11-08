<?php

namespace App\Listeners;

use App\Events\InviteInGroupEvent;
use App\Models\Notification;
use App\Services\Notifications\CreateDBNotification;
use App\Services\Notifications\PushNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class InviteInGroupListener
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
     * @param  \App\Events\InviteInGroupEvent  $event
     * @return void
     */
    public function handle(InviteInGroupEvent $event)
    {
        $data = [
            'to_user_id'        =>  $event->recipient->id,
            'from_user_id'      =>  auth()->id(),
            'notification_type' =>  'GROUP_INVITATION',
            'title' => auth()->user()->full_name ." has invited you in ". $event->group->name . " group ",
        ];
        
        $notification = Notification::where($data)->first();
        
        if( $notification )
        {
            $notification->delete();
        }
        $data['redirection_id'] =  $event->group->id ;
        
        $save_notification = app(CreateDBNotification::class)->execute($data);
        $send_push = app(PushNotificationService::class)->execute($data,[$event->recipient->device_token]);
    }
}
