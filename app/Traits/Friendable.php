<?php
namespace App\Traits;

use App\Exceptions\AppException;
use App\Models\Event;
use App\Models\EventInvitation;
use App\Models\FriendRequest;
use App\Models\GroupInvitation;
use Illuminate\Database\Eloquent\Model;
const PENDING = 0;
const ACCEPTED = 1;
const DENIED = 2;
const BLOCKED = 3;
use Illuminate\Support\Facades\DB;

trait Friendable {

    public function friends()
    {
        return $this->morphMany(FriendRequest::class, 'sender');
    }


    public function getFriendShipStatus(Model $recipient){
        if ($friendship = $this->getFriendship($recipient)) {
            if($friendship->status === ACCEPTED){
                return  'REQUEST_ACCEPTED';
            }elseif ($friendship->status === DENIED){
                return  'REQUEST_DENIED';
            }elseif ($friendship->status === PENDING){
                // $this->id;
                if($friendship->sender_id === auth()->id())
                {
                    return 'REQUEST_SENT';
                }
                return  'REQUEST_PENDING';
            }
        }
        return 'SEND_REQUEST';
    }


    public function getFriendship(Model $recipient)
    {
        return $this->findFriendship($recipient)->first();
    }


    public function sendRequest(Model $recipient){
        
        if (!$this->canBefriend($recipient)) {
            return false;
        }

        $friendship = (new FriendRequest)->fillRecipient($recipient)->fill([
            'status' => PENDING,
        ]);
        $this->friends()->save($friendship);
        return $friendship;
    }


    public function acceptRequest(Model $recipient)
    {
        
        if ($friendship = $this->getFriendship($recipient)) {
            if($friendship->status === ACCEPTED){
                throw new AppException('Request already accepted');
            }
        }
        $updated = $this->findFriendship($recipient)->whereRecipient($this)->update([
            'status' => ACCEPTED,
        ]);
        return $updated;
    }

    public function denyRequest(Model $recipient)
    {
        if ($friendship = $this->getFriendship($recipient)) {
            if($friendship->status === DENIED){
                throw new AppException('Request already rejected');
            }if($friendship->status === ACCEPTED){
                throw new AppException('Request can not be denied after accepting');
            }
        }
        $updated = $this->findFriendship($recipient)->whereRecipient($this)->update([
            'status' => DENIED,
        ]);
        $updated = $this->findFriendship($recipient);
        return $updated;
    }

    public function cancelRequest(Model $recipient){
        if($this->hasSentRequestTo($recipient)){
            FriendRequest::where('sender_id',$this->id)
                ->where('recipient_id',$recipient->id)
                ->where('status',PENDING)
                ->delete();
            return true;
        }
        throw new AppException('You haven\'t any request in pending with specified user');
    }


    public function blockUser(Model $recipient)
    {
        if ($friendship = $this->getFriendship($recipient)) {
            if($friendship->status===ACCEPTED || $friendship->status===PENDING){
                $updated = $this->findFriendship($recipient)->update([
                    'status' => BLOCKED,
                ]);
                return $updated;
            }else{
                return false;
            }

        }else{
            return false;
        }
    }


    public function unfriend(Model $recipient)
    {
        if ($friendship = $this->getFriendship($recipient)) {
            if($friendship->status===ACCEPTED ){
                $updated = $this->findFriendship($recipient)->delete();
                return $updated;
            }else{
                return false;
            }

        }else{
            return false;
        }
    }



    public function hasRequestFrom(Model $recipient)
    {

        return $this->findFriendship($recipient)->whereSender($recipient)->whereStatus(PENDING)->exists();
    }

    public function hasSentRequestTo(Model $recipient)
    {
        return FriendRequest::whereSender($this)->whereRecipient($recipient)->whereStatus(PENDING)->exists();
    }

    public function canBefriend($recipient)
    {
        if($recipient->id === $this->id){
            return  false;
        }
        if ($friendship = $this->getFriendship($recipient)) {
            if ($friendship->status === DENIED ) {
                throw new AppException('Your request has been denied already');
            }else if ($friendship->status === ACCEPTED ) {
                throw new AppException('You are already liked');
            }else if ($friendship->status === PENDING) {
                throw new AppException('You already have a request in pending');
            }
        }
        return true;
    }


    public function getFriendRequestsSent($offset=0,$limit=100)
    {
        $recipients =  FriendRequest::whereSender($this)->whereStatus(PENDING)->get(['recipient_id']);
        return $this->whereIn('id', $recipients)->select('id','full_name','avatar',
                        DB::raw('(select count(id) from follows where follower_id = "'.auth()->id().'" AND  following_id = users.id) as is_following'),
                        DB::raw('(select count(id) from follows where follower_id  = users.id) as following_count'),
        )->skip($offset)->take($limit)->get();
    }

    public function getFriendRequestsReceived($offset=0,$limit=100)
    {
        $senders =  FriendRequest::whereRecipient($this)->whereStatus(PENDING)->get(['sender_id']);
        return $this->whereIn('id', $senders)->select('id','full_name','avatar',
            DB::raw('(select count(id) from follows where follower_id = "'.auth()->id().'" AND  following_id = users.id) as is_following'),
            DB::raw('(select count(id) from follows where follower_id  = users.id) as following_count'),
        )->skip($offset)->take($limit)->get();
    }


    public function getFriends($offset=0,$limit=20)
    {
        
        return $this->getFriendsQueryBuilder()->select('id','full_name','avatar','device_token',
            DB::raw('(select count(id) from follows where follower_id = "'.auth()->id().'" AND  following_id = users.id) as is_following'),
            DB::raw('(select count(id) from follows where follower_id  = users.id) as following_count'),
        )->skip($offset)->take($limit)->get();
    }

    public function getAllFriendships()
    {
        return $this->findFriendships(null)->get();
    }

    private function findFriendship(Model $recipient)
    {
        return FriendRequest::betweenModels($this, $recipient);
    }


    private function findFriendships($status = null)
    {
        $query = FriendRequest::where(function ($query) {
            $query->where(function ($q) {
                $q->whereSender($this);
            })->orWhere(function ($q) {
                $q->whereRecipient($this);
            });
        });

        //if $status is passed, add where clause
        if (!is_null($status)) {
            $query->where('status', $status);
        }
        
        return $query;
    }


    private function getFriendsQueryBuilder($status=ACCEPTED,$group_id = null,$event_id =null)
    {

        $friendships = $this->findFriendships($status)->get(['sender_id', 'recipient_id']);
        $recipients  = $friendships->pluck('recipient_id')->all();
        $senders     = $friendships->pluck('sender_id')->all();
        $group_members = [];
        if( $group_id !=null )
        {
            $group_data     = $this->getListOfInvitedUsersInGroup($group_id)->get(['group_id','user_id']);
            $group_members = $group_data->pluck('user_id')->all();
        }
        $event_members =[];
        if( $event_id !=null )
        {
            $event_data = $this->getListOfInvitedUsersInEvent($event_id)->get(['event_id','user_id']);
            $event_members = $event_data->pluck('user_id')->all();
        }

        return $this->where('id', '!=', $this->getKey())->select('id','full_name','avatar')
                ->whereNotIn('id',$group_members)->whereNotIn('id',$event_members)->whereIn('id', array_merge($recipients, $senders));
    }

    public function getInviteGroupList($group_id)
    {
        return $this->getFriendsQueryBuilder(ACCEPTED,$group_id)->select('id','full_name','avatar',
                            DB::raw('(select count(id) from follows where follower_id = "'.auth()->id().'" AND  following_id = users.id) as is_following'),
                            DB::raw('(select count(id) from follows where follower_id  = users.id) as following_count'),
                        )->get();
    }

    public function getInviteEventList($event_id)
    {
        return $this->getFriendsQueryBuilder(ACCEPTED,null,$event_id)->select('id','full_name','avatar',
            DB::raw('(select count(id) from follows where follower_id = "'.auth()->id().'" AND  following_id = users.id) as is_following'),
            DB::raw('(select count(id) from follows where follower_id  = users.id) as following_count'),
        )->get();
    }

    private function getListOfInvitedUsersInGroup($group_id)
    {
        return GroupInvitation::where('group_id',$group_id);
    }

    private function getListOfInvitedUsersInEvent($event_id)
    {
        return EventInvitation::where('event_id',$event_id);
    }

    

}
