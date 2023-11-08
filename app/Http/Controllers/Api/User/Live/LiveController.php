<?php

namespace App\Http\Controllers\Api\User\Live;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Notifications\PushNotificationService;
use Illuminate\Http\Request;


class LiveController extends Controller
{
    public function sendNotificationToEndLiveStream()
    {
        $tokens = auth()->user()->getFriends()->pluck('device_token')->toArray();
        // return gettype($tokens);
        $data = [
            'to_user_id'        =>  '',
            'from_user_id'      =>  '',
            'notification_type' =>  'END_LIVE_STREAM',
            'title'             =>  auth()->user()->full_name ." has ended streaming",
            'redirection_id'    => ''
        ];
        foreach ($tokens as $token)
        {
            $send_push = app(PushNotificationService::class)->execute($data,[$token]);
        }

        return commonSuccessMessage("Success");   
    }

    public function sendNotificationToEndCall(Request $request)
    {
        $receiver_id = $request->receiver_id;

        $user = User::whereId($receiver_id)->first();
        if (!$user)
        {
            return commonErrorMessage("No User Found");
        }
        
        $data = [
            'to_user_id'        =>  '',
            'from_user_id'      =>  '',
            'notification_type' =>  'END_CALL',
            'title'             =>  "Call has ended call",
            'redirection_id'    => ''
        ];
        
        $send_push = app(PushNotificationService::class)->execute($data,[$user->device_token]);

        return commonSuccessMessage("Success");   
        

    }
}
