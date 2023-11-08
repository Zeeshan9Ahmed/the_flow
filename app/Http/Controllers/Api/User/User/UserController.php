<?php

namespace App\Http\Controllers\Api\User\User;

use App\Events\FollowUserEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\User\FollowUnfollowRequest;
use App\Http\Requests\Api\User\User\GetTopListRequest;
use App\Http\Requests\Api\User\User\GetUserProfileRequest;
use App\Http\Requests\Api\User\User\MakeTopFriendOrFollowerRequest;
use App\Http\Requests\Api\User\User\ProfilePageDataRequest;
use App\Http\Requests\Api\User\User\SendAttachmentRequest;
use App\Http\Resources\FollowersResource;
use App\Http\Resources\FollowFollowingResource;
use App\Http\Resources\FollowingResource;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\SearchFriendResource;
use App\Http\Resources\TopFollowersResource;
use App\Http\Resources\TopFriendsResource;
use App\Http\Resources\TopListResource;
use App\Models\Chat;
use App\Models\Event;
use App\Models\Follow;
use App\Models\Group;
use App\Models\Music;
use App\Models\Notification;
use App\Models\Post;
use App\Models\TopList;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Resources\UserResource;

class UserController extends Controller
{

    public function notifications()
    {
        $data = Notification::where('to_user_id',auth()->id());
        $data->update(['notification_is_read' => "1"]);
        $notifications = $data->latest()->get();
        
        if( $notifications->count() == 0 )
        {
            return commonErrorMessage("No Notifications ", 404);
        }

        
        return apiSuccessMessage("Notifications List" , NotificationResource::collection($notifications));
    }

    public function myProfile(){
        $profile = User::select('id','full_name','email','avatar','date_of_birth','zip_code','state','address','is_active','is_profile_complete','is_verified')
        ->withCount('followers','following')
        ->where('id',auth()->id())
        ->first();
        return apiSuccessMessage("My Profile Data", $profile);
    }

    public function profilePageData(ProfilePageDataRequest $request){
        $type = $request->type;
        $user_id = $request->user_id;
        $data = '';
        if($type ==  'post'){
            $data = Post::with('user:id,full_name,avatar')
            ->select(
                'id',
                'user_id',
                'group_id',
                  'text',
                  DB::raw('(select count(id) from likes where (user_id = "'.auth()->id().'" AND post_id = posts.id)) as is_like'),
                  'file',
                  'type',
                  'is_blocked',
                  'created_at',
                  'updated_at',
            )
            ->withCount('likes','comments')->where('user_id',$user_id)->get();
            
        }

        if($type == 'event')
        {
            $data = Event::with('user:id,full_name,avatar')->where('user_id',auth()->id())->get();
        }

        if ( $type == 'group' )
        {
            $data = Group::where('user_id',auth()->id())->get();

        }

        if ( $type == 'music' )
        {
            $data = Music::where('user_id',auth()->id())->get();
        }


        if($data->count() == 0)
        {
            return commonErrorMessage("No Data Found",404);
        }
        return apiSuccessMessage("$type Data", $data);

    }

    public function followUnFollowUser(FollowUnfollowRequest $request){
        $following_id = $request->following_id;

        if(auth()->id() == $following_id){
            return commonErrorMessage("Can Not Follow Ur Self",400);
        }

        $user = User::find($following_id);
        
        if(!$user){
            return commonErrorMessage("Can not follow User Does not exists",404);
        }
        if($user->is_verified == 0){
            return commonErrorMessage("User Account is not verified",404);
        }
        if($user->is_blocked == 1){
            return commonErrorMessage("User is Blocked",404);
        }
        
        $data = [
            'follower_id' => auth()->id(),
            'following_id' => $following_id
        ];
        
        $follow = Follow::where($data)->first();
        if(!$follow){
            
            event(new FollowUserEvent($user));
            $following = Follow::create($data);
            return apiSuccessMessage("Followed Successfully",new UserResource(User::logged_in_user()));
            // return commonSuccessMessage("Followed Successfully",200);
        }

        $unfollowing = $follow->delete();
        if($unfollowing){
            return apiSuccessMessage("UnFollowed Successfully",new UserResource(User::logged_in_user()));

            // return commonSuccessMessage("UnFollowed Successfully", 200);
        }
        

    }

    public function getUserProfile(GetUserProfileRequest $request)
    {
        $user_id = $request->user_id;
        
        if( $user_id == auth()->id() )
        {
            return commonErrorMessage("Invalid user id");
        }
        $user = User::find($user_id);
        if( !$user )
        {
            return commonErrorMessage("No User Found",404);
        }
        $user_profile = User::select('id','full_name','avatar',
                            DB::raw('(select count(id) from follows where following_id = '.auth()->id().' AND follower_id = users.id) as is_following'),
                            )
                            ->withCount('following')
                            ->whereId($user->id)
                            ->first();
            
        
                            
        
        return apiSuccessMessage("User Profile Data",new SearchFriendResource($user_profile));
    }

    public function chatList(Request $request)
    {
        
        $get_chat_list_1 = DB::table('st_chat')->select(
            'users.id',
            'users.full_name',
            'users.avatar',
            DB::raw('(select chat_message  from st_chat as st where st.chat_sender_id = `users`.`id` OR st.chat_reciever_id = `users`.`id` order by created_at desc limit 1) as chat_message'),
            DB::raw('(select chat_type  from st_chat as st where st.chat_sender_id = `users`.`id` OR st.chat_reciever_id = `users`.`id` order by created_at desc limit 1) as chat_message'),
            'st_chat.chat_read_at',
            'st_chat.created_at'
        )
        ->leftJoin('users', 'users.id', '=', 'st_chat.chat_reciever_id')
        ->where('st_chat.chat_sender_id', $request->user_id);
        
        $get_chat_list_2 = DB::table('st_chat')->select(
            'users.id',
            'users.full_name',
            'users.avatar',
            DB::raw('(select chat_message  from st_chat as st where st.chat_sender_id = `users`.`id` OR st.chat_reciever_id = `users`.`id` order by created_at desc limit 1) as chat_message'),
            DB::raw('(select chat_type  from st_chat as st where st.chat_sender_id = `users`.`id` OR st.chat_reciever_id = `users`.`id` order by created_at desc limit 1) as chat_message'),
            
            'st_chat.chat_read_at',
            'st_chat.created_at'
        )
        ->leftJoin('users', 'users.id', '=', 'st_chat.chat_sender_id')
        ->where('st_chat.chat_reciever_id', $request->user_id)
        ->union($get_chat_list_1);
        
        $groupby = DB::query()->fromSub($get_chat_list_2, 'p_pn')
            ->select('id', 'full_name', 'avatar', 'chat_message','chat_type', 'chat_read_at', 'created_at')
            ->groupBy('id')->orderBy('created_at','desc')->get();
        if( $groupby->count() == 0 )
        {
            return commonErrorMessage("No chat list found",404);
        }
            
        return apiSuccessMessage("Chat List ", $groupby);
            
    }

    public function sendAttachment(SendAttachmentRequest $request)
    {
        $type = $request->chat_type;
        
        if ( $type == 'image' )
        {
            $request->validate([
                'attachments.*' => 'required|image',
            ]);
        }

        if ( $type == 'video' )
        {
            $request->validate([
                'attachments.*' => 'required|mimes:mp4,ogx,oga,ogv,ogg,webm|max:102400'
            ]);
        }


        $receiver_id = $request->reciever_id;
        $sender_id = $request->sender_id;
        $ids = [];
        if($request->hasFile('attachments'))
        {
            foreach ( $request->file('attachments') as $attachment )
            {
                $uuid = Str::uuid();
                $imageName = $uuid.time().'.'.$attachment->getClientOriginalExtension();
                $attachment->move(public_path('/uploadedattachment'), $imageName);
                $image = asset('public/uploadedattachment')."/".$imageName;
                
                $attachment = [
                    'chat_sender_id' => $sender_id,
                    'chat_reciever_id' => $receiver_id,
                    'chat_message' => $image ,
                    'chat_type' => $type,
                ];
    
                $ids[] = Chat::create($attachment)->id;
            }
            

        }

                $ids_in_string = collect($ids)->implode(',');
                    $messages = DB::select(
                        "SELECT 
                        u.full_name,
                        u.avatar, 
                        (select device_token from users where id = $receiver_id) as user_device_token,
                        c.*
                        FROM users AS u
                        JOIN st_chat AS c
                        ON u.id = c.chat_sender_id
                        WHERE  c.chat_id in ($ids_in_string)");

            return response()->json([
                "status" => 1 ,
                "message" => "Attachment Sent Successfully",
                "data" => $messages
              ]);
            
    }

    public function getFollowersList()
    {
            $followers_list = User::select('users.id','users.full_name','users.avatar')->with('followers',function($query){
            $query->select('users.id','users.full_name','users.avatar',
            DB::raw('(select count(id) from follows where follower_id = "'.auth()->id().'" AND  following_id = users.id) as is_following'),
            DB::raw('(select count(id) from follows where follower_id  = users.id) as following_count'),
            );
        })->where('id',auth()->id())->first();
        if ( $followers_list->followers->count() == 0 )
        {
            return commonErrorMessage("No Followers List Found",400);
        }

        return apiSuccessMessage("Followers List", new FollowersResource($followers_list));
    }

    public function getFollowingList(){
        $following_list = User::select('users.id','users.full_name','users.avatar')->with('following',function($query){
            $query->select('users.id','users.full_name','users.avatar',
            DB::raw('(select count(id) from follows where follower_id = "'.auth()->id().'" AND  following_id = users.id) as is_following'),
            DB::raw('(select count(id) from follows where follower_id  = users.id) as following_count'),
            );
        })->where('id',auth()->id())->first();
        
        if ( $following_list->following->count() == 0 )
        {
            return commonErrorMessage("No Following List Found",400);
        }

        return apiSuccessMessage("Following List", new FollowingResource($following_list));
    }

    public function getTopList(GetTopListRequest $request)
    {
        $user_id = $request->user_id;
        $type = $request->type;

        $data = User::select('users.id','users.full_name','users.avatar')->with("$type",function($query){
                $query->select(
                    'users.id','users.full_name','users.avatar',
                DB::raw('(select count(id) from follows where follower_id = "'.auth()->id().'" AND  following_id = users.id) as is_following'),
                DB::raw('(select count(id) from follows where follower_id  = users.id) as following_count'),
            );
        })->where('id',$user_id)->first();
        
        if ( $data->$type->count() == 0)
        {
            return commonErrorMessage("No $type List Found",400);

        }

        return apiSuccessMessage("$type List", $type=='top_followers' ? new TopFollowersResource($data): new TopFriendsResource($data));
    }

    public function makeTop(MakeTopFriendOrFollowerRequest $request)
    {
        $other_user_id = $request->other_user_id;
        $type = $request->type;
        $user = User::find($other_user_id);
        if(!$user){
            return commonErrorMessage("No User Found",400);
        }
        if($other_user_id == auth()->id()){
            return commonErrorMessage("Can Not Make Top Urself",400);
        }
        $data = [
            'user_id' => auth()->id(),
            'other_user_id' => $other_user_id,
            'type' => $type
        ];
        $check = TopList::where($data)->first();
        if(!$check){
            $count = $this->countTopFollowersOrFriends($type);
            if($count == '5'){
                return commonErrorMessage("Can not make Top $type more than 5",400);
            }
            $addInToTopList = TopList::create($data);
            return commonSuccessMessage("Added in Top $type List");
        }
        
        $deleteFromTopList = $check->delete();
        return commonSuccessMessage("Deleted from Top $type List");

    }

    protected function countTopFollowersOrFriends(String $type)
    {
        if($type== 'friend'){
            return auth()->user()->withCount('top_friends')->first()->top_friends_count;
        }
        if($type== 'follower'){
            return auth()->user()->withCount('top_followers')->first()->top_followers_count;
        }
    }
}
