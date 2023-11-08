<?php

namespace App\Listeners;

use App\Events\AcceptGroupRequestEvent;
use App\Models\Notification;
use App\Models\User;
use App\Services\Notifications\CreateDBNotification;
use App\Services\Notifications\PushNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AcceptGroupRequestListener
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
     * @param  \App\Events\AcceptGroupRequestEvent  $event
     * @return void
     */
    public function handle(AcceptGroupRequestEvent $event)
    {
        $data = [
            'to_user_id'        =>  $event->recipient->user_id,
            'from_user_id'      =>  auth()->id(),
            'notification_type' =>  'GROUP_INVITATION_ACCEPT',
            'title' => auth()->user()->full_name ." has accepted invitation in ". $event->recipient->name . " group ",
        ];
        
        $notification = Notification::where($data)->first();
        
        if( $notification )
        {
            $notification->delete();
        }
        $data['redirection_id'] =  $event->recipient->id ;
        $user = User::find($event->recipient->user_id);
        $save_notification = app(CreateDBNotification::class)->execute($data);
        $send_push = app(PushNotificationService::class)->execute($data,[$user->device_token]);
    }
}
