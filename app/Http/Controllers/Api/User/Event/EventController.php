<?php

namespace App\Http\Controllers\Api\User\Event;

use App\Events\InviteInEventEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Event\CreateEventRequest;
use App\Http\Requests\Api\User\Event\EventDetailRequest;
use App\Http\Requests\Api\User\Event\EventInviteListRequest;
use App\Http\Requests\Api\User\Event\InviteInEventRequest;
use App\Http\Requests\Api\User\Event\SearchEventRequest;
use App\Http\Resources\EventResource;
use App\Http\Resources\SearchFriendResource;
use App\Models\Event;
use App\Models\EventInvitation;
use App\Models\User;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function createEvent(CreateEventRequest $request){
        $data = $request->all();
        $data['user_id'] = auth()->id();
        $avatar = null;
        if($request->hasFile('image')){
            $imageName = time().'.'.$request->image->getClientOriginalExtension();
            $request->image->move(public_path('/eventimages'), $imageName);
            $avatar = asset('public/eventimages')."/".$imageName;
        }
        $data['image'] = $avatar;
        $event = Event::create($data);
        if($event){
            return apiSuccessMessage("Event Created Successfully", $event);
        }
        return commonErrorMessage("Something Went Wrong while creating Evnet",400);
    }

    public function getAllEvents(){
        $friends = auth()->user()->getFriends()->pluck('id');
        
        $events = Event::with('user')->whereIn('user_id',$friends)->get();
        if( $events->count() ==0 ){
            return commonErrorMessage("No Events Found",400);
        }

        return apiSuccessMessage("All Events List", EventResource::collection($events));
    }

    public function searchEvent(SearchEventRequest $request)
    {
        $friends = auth()->user()->getfriends()->pluck('id');
        $search = $request->search;
        $searchedEvents = Event::with('user')
            ->where('title', 'LIKE', "%{$search}%")->orWhere(function($query) use($search){
                $query->whereHas('user',function($q) use($search)
                {
                    $q->where('full_name','LIKE', '%'.$search. '%');
                });
            }) 
            ->whereIn('user_id',$friends)
            ->get();
        
        if($searchedEvents->count() == 0){
            return commonErrorMessage("No Data Found for that search", 400);
        }

        return apiSuccessMessage("Search Events", EventResource::collection($searchedEvents));
    }

    public function eventDetail(EventDetailRequest $request){
        $event_id = $request->event_id;
        $event = Event::with('user:id,full_name,email,address,zip_code,state,avatar')->where('id',$event_id)->first();
        
        if(! $event){
            return commonErrorMessage("Not Event Found", 400);
        }

        return apiSuccessMessage("Event Detail", $event);
    }

    public function eventInviteList(EventInviteListRequest $request)
    {
        $event = Event::find($request->event_id);
        if( !$event )
        {
            return commonErrorMessage("No Event Found",404);
        }

        if( $event->user_id != auth()->id() )
        {
            return commonErrorMessage("Can not Invite",400);
        }

        $event_invitation_list = auth()->user()->getInviteEventList($event->id);
        if ( $event_invitation_list->count() == 0 )
        {
            return commonErrorMessage("No List to Invite",404);
        }
        return apiSuccessMessage("Event Invitation List", SearchFriendResource::collection($event_invitation_list));
    }

    public function inviteInEvent(InviteInEventRequest $request)
    {
        $user_id = $request->user_id;
        $event_id = $request->event_id;
        if(auth()->id() == $user_id){
            return commonErrorMessage("Can not invite urself in the group",400);
        }
        $event = Event::find($event_id);
        if( !$event ){
            return commonErrorMessage("No Event Found",404);
        }
        
        $user = User::find($user_id);
        if( !$user ){
            return commonErrorMessage("No User Found",404);
        }
        
        $data = [
            'user_id' => $user_id,
            'event_id' => $event_id,
        ];
        
        if( $this->checkInvitationStatus($data))
        {
            return commonErrorMessage("Already Invited",400);
        }

        $members_list = auth()->user()->getInviteEventList($event->id);
        if($members_list->contains('id',$user->id) == false){
            return commonErrorMessage("can not Invite ",400);
        }
        

        
        
        $send_invitation = EventInvitation::create($data);
        if($send_invitation){
    
            event( new InviteInEventEvent($event,$user));

            return commonSuccessMessage("Invite sent Successfully");
        }

        return commonErrorMessage("Something went wrong",400);
    }

    protected function checkInvitationStatus(array $data)
    {
        return EventInvitation::where($data)->exists();
    }
}
