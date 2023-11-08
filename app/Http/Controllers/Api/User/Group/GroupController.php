<?php

namespace App\Http\Controllers\Api\User\Group;

use App\Events\AcceptGroupRequestEvent;
use App\Events\InviteInGroupEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Group\AcceptOrRejectGroupRequestRequest;
use App\Http\Requests\Api\User\Group\CreateGroupRequest;
use App\Http\Requests\Api\User\Group\DeleteGroupRequest;
use App\Http\Requests\Api\User\Group\GetGroupMembersRequest;
use App\Http\Requests\Api\User\Group\GetGroupWisePostRequest;
use App\Http\Requests\Api\User\Group\GroupDetailRequest;
use App\Http\Requests\Api\User\Group\InviteInGroupRequest;
use App\Http\Requests\Api\User\Group\InviteMembersListRequest;
use App\Http\Requests\Api\User\Group\LeaveGroupRequest;
use App\Http\Requests\Api\User\Group\SearchCommunityRequest;
use App\Http\Resources\CommunityPostsResource;
use App\Http\Resources\GroupPostResource;
use App\Http\Resources\SearchFriendResource;
use App\Models\Comment;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    public function createGroup(CreateGroupRequest $request)
    {
        $data = $request->only(['name','description'])+['user_id' => auth()->id(),'is_private' => "1"];
        $file = null;
        if($request->hasFile('image')){
            $fileName = time().'.'.$request->image->getClientOriginalExtension();
            $request->image->move(public_path('/uploadedfile'), $fileName);
            $file = asset('public/uploadedfile')."/".$fileName;
        }
        $data['image'] = $file;
        $group = Group::create($data);
        return apiSuccessMessage("Created Successfully", $group);
        
    }

    public function AuthGroups()
    {
        $groups = Group::where('user_id',auth()->id())->get();
        if( $groups->count() == 0 )
        {
            return commonErrorMessage("No Group List Found",404);
        }
        
        return apiSuccessMessage("Group Lists", $groups);
    }

    public function groupDetail(GroupDetailRequest $request)
    {
        $group_detail = Group::select('id', 'name','image')->where('id',$request->group_id)->first();

        if (!$group_detail) return commonErrorMessage("No Group Found");
        return apiSuccessMessage("Group Detail", $group_detail);
    }
    public function searchCommunityPosts(SearchCommunityRequest $request)
    {
        $search = $request->search;
        $joined_groups_ids = GroupInvitation::where('user_id',auth()->id())->where('status','accept')->pluck('group_id');
        $search_community =  Post::select('posts.id','posts.user_id','posts.text','posts.group_id','posts.file','posts.type','posts.created_at',
                                    DB::raw('(select count(id) from likes where user_id = "'.auth()->id().'" AND post_id = posts.id) as is_like'),
                                    )->withCount('likes','comments')->with('group_name')->with('author')
                                ->Where(function($query) use($search){
                                    $query->whereHas('group_name',function($q) use($search)
                                    {
                                        $q->where('name','LIKE', '%'.$search. '%');
                                    });
                                })->orWhere(function($query) use($search){
                                    $query->whereHas('author',function($q) use($search)
                                    {
                                        $q->where('full_name','LIKE', '%'.$search. '%');
                                    });
                                })
                                ->whereIn('group_id',$joined_groups_ids)->orderBy('id','desc')->latest()->get();
        
        
        if ( $search_community->count() == 0 )
        {
            return commonErrorMessage("No result Found",404);
        }
        return apiSuccessMessage("Search Result", CommunityPostsResource::collection($search_community));
    }

    public function getGroupMembers(GetGroupMembersRequest $request)
    {
        $group = Group::find($request->group_id);
        if( !$group )
        {
            return commonErrorMessage("No Group Found",404);
        }

        $group_members_ids = GroupInvitation::where('group_id',$group->id)->where('status','accept')->pluck('user_id');
        
        $group_members = User::select('id','full_name','avatar',
                                DB::raw('(select count(id) from follows where follower_id = "'.auth()->id().'" AND  following_id = users.id) as is_following'),
                                DB::raw('(select count(id) from follows where follower_id  = users.id) as following_count'),
                            )->whereIn('id',$group_members_ids)->get();
        
        if( $group_members->count() == 0 )
        {
            return commonErrorMessage("No Members in this group",404);
        }

        return apiSuccessMessage("Group Members List", SearchFriendResource::collection($group_members));
    }

    public function getGroupWisePosts(GetGroupWisePostRequest $request)
    {
        $group_id = $request->group_id;
        $group = Group::find($group_id);
        if( !$group )
        {
            return commonErrorMessage("No Group Found",404);
        }

        $group_posts = Post::select('posts.id','posts.group_id','posts.user_id','posts.text','posts.file','posts.type','posts.created_at',
                            DB::raw('(select count(id) from likes where user_id = "'.auth()->id().'" AND post_id = posts.id) as is_like'),
                        )->withCount('likes','comments')
                        ->with('author')
                        ->where('group_id',$group->id)
                        ->latest()->get();
        
        if( $group_posts->count() == 0 )
        {
            return commonErrorMessage("No Posts Found in the group",404);
        }
        return apiSuccessMessage("Group Posts", GroupPostResource::collection($group_posts));
    }


    public function getCommunityPosts()
    {
        $joined_groups_ids = GroupInvitation::where('user_id',auth()->id())->where('status','accept')->pluck('group_id');
        $group_posts = Post::select('posts.id','posts.user_id','posts.text','posts.group_id','posts.file','posts.type','posts.created_at',
                            DB::raw('(select count(id) from likes where user_id = "'.auth()->id().'" AND post_id = posts.id) as is_like'),
                            )->withCount('likes','comments')
                            ->with('group_name')->with('author')->whereIn('group_id',$joined_groups_ids)->orderBy('id','desc')->latest()->get();
        
        if ( $group_posts->count() == 0 ) 
        {
            return commonErrorMessage("No Community Posts Found",404);
        }
        
        
        return apiSuccessMessage("Community Posts", CommunityPostsResource::collection($group_posts));
    }
    public function inviteMembersList(InviteMembersListRequest $request)
    {
        $group_id = $request->group_id;
        $group = Group::find($group_id);
        if(!$group){
            return commonErrorMessage("No Group Found",404);
        }
        if(auth()->id() !== $group->user_id){
            return commonErrorMessage("You have no permission, for invite Members list",400);
        }
        $members_list = auth()->user()->getInviteGroupList($group_id);
        if($members_list->count() ==0){
            return commonErrorMessage("No Invite Members List",400);
        }
        return apiSuccessMessage("Group Invite Members List", SearchFriendResource::collection($members_list));
    }

    public function inviteInGroup(InviteInGroupRequest $request)
    {
        $user_id = $request->user_id;
        $group_id = $request->group_id;
        if(auth()->id() == $user_id){
            return commonErrorMessage("Can not invite urself in the group",400);
        }
        $group = Group::find($group_id);
        if(!$group){
            return commonErrorMessage("No Group Found",404);
        }
        
        $user = User::find($user_id);
        if( !$user ){
            return commonErrorMessage("No User Found",404);
        }
        $data = [
            'user_id' => $user_id,
            'group_id' => $group_id,
            // 'status' => 'pending'
        ];
        
        if( $this->checkInvitationStatus($data))
        {
            return commonErrorMessage("Already Invited",400);
        }

        $members_list = auth()->user()->getInviteGroupList($group_id);
        
        if($members_list->contains('id',$user_id) == false ){
            return commonErrorMessage("can not Invite ",400);
        }

        
        $send_invitation = GroupInvitation::create($data);
        if( $send_invitation )
        {
            event( new InviteInGroupEvent( $group , $user ));
            return commonSuccessMessage("Invite sent Successfully");
        }

        return commonErrorMessage("Something went wrong",400);

    }

    protected function checkInvitationStatus(array $data)
    {
        return GroupInvitation::where($data)->exists();
    }


    public function acceptOrRejectGroupRequest(AcceptOrRejectGroupRequestRequest $request)
    {
        $type = $request->type;
        $data = $request->only(['user_id','group_id']);
        $group = Group::find($data['group_id']);

        if( !$group )
        {
            return commonErrorMessage("No Group Found ", 404);
        }

        if($type == 'accept')
        {
            
            return $this->acceptGroupRequest($data);
        }

        return $this->rejectGroupRequest($data);
        
    }

    public function leaveGroup(LeaveGroupRequest $request)
    {
        $data = $request->only(['user_id','group_id']);
        $group_invitation = $this->hasGroupRequestTo($data);
        
        if( !$group_invitation )
        {
            return commonErrorMessage("you are not a member of this group",400);
        }
        
        if( $group_invitation->status == 'pending' )
        {
            return commonErrorMessage("Can not leave group before accepting the group request",400);
        }
        
        $leave_group = $group_invitation->delete();
        if( $leave_group )
        {
            return commonSuccessMessage("Group Left Successfully");
        }
    }

    public function deleteGroup(DeleteGroupRequest $request)
    {
        $group_id = $request->group_id;
        $group = Group::find($group_id);
        if( !$group )
        {
            return commonErrorMessage("No Group find",404);
        }
        if( auth()->id() !== $group->user_id )
        {
            return commonErrorMessage("Can not delete the group, the group does not belong to u",400);
        }

        $this->deleteGoupMembers($group_id)->delete();
        $this->deleteGroupPosts($group_id)->delete();
        $delete_group = $group->delete();
        if( $delete_group )
        {
            return commonSuccessMessage("Group Deleted Successfully");
        }

        return commonErrorMessage("Something Went wrong",400);
    }

    protected function deleteGoupMembers(int $group_id)
    {
        return GroupInvitation::where('group_id',$group_id);
    }

    protected function deleteGroupPosts(int $group_id)
    {
        $posts = Post::where('group_id',$group_id);
        $post_ids = $posts->pluck('id')->toArray();
        
        $this->deletePostComments($post_ids)->delete();
        $this->deletePostLikes($post_ids)->delete();
        return $posts;
    }

    protected function deletePostComments(array $post_ids)
    {
        return Comment::whereIn('post_id',$post_ids)->orWhereIn('parent_id',$post_ids);
    }

    protected function deletePostLikes(array $post_ids)
    {
        return Like::whereIn('post_id',$post_ids);
    }

    protected function acceptGroupRequest($data)
    {
        $invitation = $this->hasGroupRequestTo($data);
        if( !$invitation ) 
        {
            return commonErrorMessage("You have no invitation to accept to this group",400);
        }
        if($invitation->status == 'pending')
        {
            $invitation->status = 'accept';
            
            if( $invitation->save() )
            {
                $group = Group::find($data['group_id']);
                event( new AcceptGroupRequestEvent($group));

                return commonSuccessMessage("Invitation Accepted Successfully");
            }
            
        }
        return commonSuccessMessage("You have already accepted the request");
    }

    protected function rejectGroupRequest($data)
    {
        $invitation = $this->hasGroupRequestTo($data);
        if( !$invitation )
        {
            return commonErrorMessage("You have no invitation to reject to this group",400);
        }

        if( $invitation->status == 'accept' )
        {
            return commonErrorMessage("Can not reject the request after accepting",400);
        }

        $reject_invitation = $invitation->delete();
        
        if( $reject_invitation )
        {
            return commonSuccessMessage("Invitation Request Rejected successfully");
        }
    }

    protected function hasGroupRequestTo(array $data)
    {
        return GroupInvitation::where($data)->first();
    }

}
