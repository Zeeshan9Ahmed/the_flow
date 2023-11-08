<?php

namespace App\Http\Controllers\Api\User\Friends;

use App\Events\AcceptFriendRequestEvent;
use App\Events\RejectFriendRequestEvent;
use App\Events\SendFriendRequestEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Friends\AcceptFriendRequestRequest;
use App\Http\Requests\Api\User\Friends\FriendListRequest;
use App\Http\Requests\Api\User\Friends\SendFriendRequestRequest;
use App\Http\Requests\Api\User\User\SearchUserRequest;
use App\Http\Resources\FriendListResource;
use App\Http\Resources\SearchFriendResource;
use App\Http\Resources\TestCollection;
use App\Models\Follow;
use App\Models\Friend;
use App\Models\FriendRequest;
use App\Models\User;
use App\Services\Notifications\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PHPUnit\Util\Test;
use App\Http\Resources\UserResource;

const PENDING = 0;
const ACCEPTED = 1;
const DENIED = 2;
const BLOCKED = 3;
class FriendController extends Controller
{
    private $notification;
    public function __construct(PushNotificationService $notification)
    {
        $this->notification = $notification;
    }
    public function searchUser(SearchUserRequest $request){
        $keyword = $request->keyword;
        $users = User::select('users.id','users.full_name','users.avatar',
                    DB::raw('(select count(id) from follows where follower_id = "'.auth()->id().'" AND  following_id = users.id) as is_following'),
                    DB::raw('(select count(id) from follows where follower_id  = users.id) as following_count'),
                )->where('id','!=',auth()->user()->id)
                ->where('full_name','LIKE',$keyword.'%')
            ->orderBy('id','DESC')->get();
        if ( $users->count() == 0 ) 
        {
            return commonErrorMessage("No User Found", 404);
        }

        return apiSuccessMessage("Users List", SearchFriendResource::collection($users));
    }

    public function friendList(FriendListRequest $request)
    {
        $type = $request->type;
        
        if($type == 'recieved'){
            
            $recieved_friend_list = auth()->user()->getFriendRequestsReceived();
            $recieved = SearchFriendResource::collection($recieved_friend_list);
            
            if($recieved_friend_list->count() == 0){
                return commonErrorMessage("No  Recieved Friend List Found",404);
            }
    
            return apiSuccessMessage("Recieved Friends List",$recieved);    
        }
        
        if($type == 'pending'){
            
            $pending_friend_list = auth()->user()->getFriendRequestsSent();
            $mutual= SearchFriendResource::collection($pending_friend_list);
            
            if($pending_friend_list->count() == 0){
                return commonErrorMessage("No  Pending Friend List Found",404);
            }
    
            return apiSuccessMessage("Pending Friends List",$mutual);    
        }
        
        
        $friends_list = auth()->user()->getFriends();
        $mutual= SearchFriendResource::collection($friends_list);
        

        if($friends_list->count() == 0){
            return commonErrorMessage("No Friend List Found",404);
        }

        return apiSuccessMessage("Friends List",$mutual);
        
    }
    public function sendRequest(SendFriendRequestRequest $request)
    {
        $recipient_id = $request->recipient_id;
        $user = Auth::user();
        if($recipient_id == auth()->id()){
            return commonErrorMessage("Sender ANd Reciever can not be same",400);
        }
        
        $recipient = User::find($recipient_id);
        
        if(!$recipient){
            return commonErrorMessage("Invalid recipient Id, User Not Found",404);
        }
        if($recipient->is_verified == 0){
            return commonErrorMessage("User Account is not verified",400);  
        }
        
        $send_request = $user->sendRequest($recipient);
        
        if($send_request){
            event(new SendFriendRequestEvent($recipient,$user));
            return commonSuccessMessage("Freind Request Sent Successfully",200);
        }
        return commonErrorMessage("Request Failed ",400);
    }

    public function cancelRequest()
    {
        return 'hehehehehhehe';
    }

    public function acceptRequest(AcceptFriendRequestRequest $request)
    {
        $recipient_id = $request->recipient_id;

        $user = Auth::user();
        
        if(auth()->id() == $recipient_id)
        {
            return commonErrorMessage("Sender can not be accepter",400);
        }

        $recipient = User::find($recipient_id);
        
        if(!isset($recipient)){
            return commonErrorMessage("Invalid Sender iD",400);
        }
        $request_accepted = $user->acceptRequest($recipient);
        
        if($request_accepted)
        {
            $this->makeFollowAndFollowing($recipient_id);    
          event(new AcceptFriendRequestEvent( $recipient , $user ));
          return apiSuccessMessage("request Accepted",new UserResource(User::logged_in_user()));
          return commonSuccessMessage("request Accepted");
        }
        
        return commonErrorMessage("Failed To accept the request",400);
    }


    private function makeFollowAndFollowing($following_id)
    {
        
        $this->follow($following_id);

        $this->following($following_id); 
    }

    protected function following($follower_id)
    {
        
        $following = Follow::firstOrCreate(['follower_id' => $follower_id, 'following_id' => auth()->id()]);
        
    }

    protected function follow($following_id) 
    {
        $follow = Follow::firstOrCreate(['following_id' => $following_id, 'follower_id' => auth()->id()]);
        
    }


    public function rejectRequest(Request $request)
    {
        $recipient_id = $request->recipient_id;
        
        $user = Auth::user();
        $recipient = User::find($recipient_id);
        
        if(!isset($recipient)){
            return commonErrorMessage("Invalid Sender iD",400);
        }

        $request_rejected = $user->denyRequest($recipient);
        if($request_rejected){
            $request_rejected->delete();
            return commonSuccessMessage("Request Rejected Successfully");
        }
        
        return commonErrorMessage("Failed to reject the request",400);
        
    }

    public function unFriend(Request $request)
    {
        $recipient = User::find($request->recipient_id);
        if(!$recipient){
            return commonErrorMessage("Invalid Recipeint",400);
        }
        $user = Auth::user();
        
        $unfriend = $user->unfriend($recipient);
        if($unfriend)
        {
            $this->removeFromFollowAndFollowingList($recipient->id);
          event(new RejectFriendRequestEvent( $recipient , $user ));
          return apiSuccessMessage("User Unfriend",new UserResource(User::logged_in_user()));

            return commonSuccessMessage("User Unfriend");
        }
        return commonErrorMessage("Failed To unfriend",400);
    }

    protected function removeFromFollowAndFollowingList($following_id)
    {
        Follow::where(['following_id' => $following_id, 'follower_id' => auth()->id()])->orWhere(['following_id' => auth()->id(), 'follower_id' => $following_id])->delete();
    }

}
