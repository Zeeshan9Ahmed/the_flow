<?php


namespace App\Services\Notifications;


use Carbon\Carbon;

class PushNotificationService 
{


    

    public function execute($data,$token)
    {
        
        $message = $data['title'];
        
        $date = Carbon::now();
        $header = [
            'Authorization: key= AAAAJen85_U:APA91bFgzqk24VwgacZ1OZ2h06NGNMw6jL6IctkhnsjIi8rS0AiNn149wqrWNFEBxQtcjH5MUNlGkYpuxAX9mwHs7LU9uB4RWv_W7Cv-UnTobZB_7WTyZFofQx4XvEKOMH74hQ479eLk',
            'Content-Type: Application/json'
        ];
        $notification = [
            'title' => 'The Flow',
            'body' =>  $message,
            'icon' => '',
            'image' => '',
            'sound' => 'default',
            'date' => $date->diffForHumans(),
            'content_available' => true,
            "priority" => "high",
            'badge' =>0
        ];
        if (gettype($token) == 'array') {
            
            $payload = [
                'registration_ids' => (array)$token,
                'data' => (object)$data,
                'notification' => (object)$notification
            ];
        } else {

            $payload = [
                'to' => $token,
                'data' => (object)$data,
                'notification' => (object)$notification
            ];
        }
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $header
        ));
        // return true;
        $response = curl_exec($curl);
        $d  =[ 'res'=>$response,'data'=>$data];
        
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

}
