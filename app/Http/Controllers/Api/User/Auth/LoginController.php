<?php

namespace App\Http\Controllers\Api\User\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Contracts\UserLoginInterface;
use App\Http\Requests\Api\User\Auth\LoginRequest;
use App\Http\Requests\Api\User\Auth\SocialLoginRequest;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{
    private $auth;
    public function __construct(UserLoginInterface $auth){
        $this->auth = $auth;
    }
    public function login(LoginRequest $request){
        return $this->auth->login($request);
    }
    public function socialLogin(SocialLoginRequest $request){
        return $this->auth->socialLogin($request);
    }
    public function logout(){
        return $this->auth->logout();
    }

    public function notifications(){
        
        $notifications = Notification::where('reciever_id', Auth::user()->id)->get(['id', 'title', 'description', 'created_at']);
        
        
        foreach($notifications as $key => $notification){
            Notification::whereId($notification->id)->update(['seen' => '1']);
            
            $notifications[$key]->new_date = $notification->created_at->format('l, F d, Y');
            $notifications[$key]->new_time = $notification->created_at->format('g:i A'); 
        }

        if($notifications == '[]'){
            return commonErrorMessage("No Notifications Found", 400);
        }
        return apiSuccessMessage("User Notifications", $notifications);
    }

    public function delete_account()
    {
        if ( auth()->user()->delete() )
        {
            return commonSuccessMessage("Account Deleted Successfully");
        }
        
    }
}
