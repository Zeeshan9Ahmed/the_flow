<?php

namespace App\Http\Controllers\Api\User\Post;

use App\Events\CommentOnPostEvent;
use App\Events\LikePostEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Post\CommentOnPostRequest;
use App\Http\Requests\Api\User\Post\CreatePostRequest;
use App\Http\Requests\Api\User\Post\DeletePostRequest;
use App\Http\Requests\Api\User\Post\EditPostRequest;
use App\Http\Requests\Api\User\Post\LikePostRequest;
use App\Http\Requests\Api\User\Post\PostCommentsRequest;
use App\Http\Requests\Api\User\Post\SearchPostRequest;
use App\Http\Resources\FollowingPostsResource;
use App\Models\Comment;
use App\Models\Group;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function createPost(CreatePostRequest $request)
    {
        
        $group_id = null;
        if($request->group_id){
            $group =  Group::find($request->group_id);
            if(!$group){
                return commonErrorMessage("Sorry, No Group Found",400);
            }
            $group_id = $group->id;
        }
        $file = null;
        if(!empty($request->type)){
            if($request->hasFile('file')){
                $fileName = time().'.'.$request->file->getClientOriginalExtension();
                $request->file->move(public_path('/uploadedfile'), $fileName);
                $file = asset('public/uploadedfile')."/".$fileName;
            }

        }
        
        $data = [
            'user_id' => auth()->id(),
            'group_id' => $group_id,
            'file' => $file,
            'text' => $request->text,
            'type' => $request->type
        ];
        
        $post = Post::create($data);
        return apiSuccessMessage("Post Created Successfully", $post);
    }

    public function editPost(EditPostRequest $request)
    {
        $post_id = $request->post_id;

        $post = Post::find($post_id);
        if( !$post )
        {
            return commonErrorMessage("No Post Found to Edit",404);
        }
        if( $post->user_id != auth()->id() ){
            return commonErrorMessage("Can not edit this post, post does not belongs to you",400);
        }
        
        $post->text = $request->text;
        if($request->hasFile('file')){
            $fileName = time().'.'.$request->file->getClientOriginalExtension();
            $request->file->move(public_path('/uploadedfile'), $fileName);
            $file = asset('public/uploadedfile')."/".$fileName;
            $post->file = $file;
        }
        $post->type = $request->type;
        
        if( $post->save() )
        {
            return apiSuccessMessage("Post Updated Successfully",$post);
        }
        return commonErrorMessage("SOmething Went Wrong while updating data",40);
        
    }

    public function deletePost(DeletePostRequest $request)
    {
        $post_id = $request->post_id;

        $post = Post::find($post_id);
        if( !$post )
        {
            return commonErrorMessage("No Post Found to delete",404);
        }
        if( $post->user_id != auth()->id() )
        {
            return commonErrorMessage("Can not delete this post, post does not belongs to you",400);
        }
        $this->deletePostComments([$post->id])->delete();
        $this->deletePostLikes([$post->id])->delete();
        $post->delete();
        return commonSuccessMessage("Post Deleted Successfully");
    }

    protected function deletePostComments(array $post_ids)
    {
        return Comment::whereIn('post_id',$post_ids)->orWhereIn('parent_id',$post_ids);
    }

    protected function deletePostLikes(array $post_ids)
    {
        return Like::whereIn('post_id',$post_ids);
    }

    public function commentOnPost(CommentOnPostRequest $request){
        $data = $request->only(['post_id','comment']) + ['user_id' => auth()->id()];
        $post = Post::find($request->post_id);
        if(!$post){
            return commonErrorMessage("No Post Found",404);
        }
        
        if($request->parent_id){
            $comment = Comment::where('id',$request->parent_id)->first();
            if(!$comment){
                return commonErrorMessage("No Comment Found For that Id", 404);
            }
            $data['parent_id'] = $request->parent_id;
        }
        
        $comment = Comment::create($data);
        
        $recipient = User::find($post->user_id);

        event(new CommentOnPostEvent( $post->id , $recipient ));
        
        return apiSuccessMessage("Commented On Post Successfully", $comment);
        
    }

    public function likeUnlikePost(LikePostRequest $request){
        $post_id = $request->post_id;
        $post = Post::find($post_id);
        
        if(!$post){
            return commonErrorMessage("No Post Found",404);
        }
        
        $like = Like::where(['user_id' => auth()->id(),'post_id' => $post_id,])
                ->first();
            

        if( !$like )
        {
            $data = [
                'post_id' => $post_id,
                'user_id' => auth()->id()
            ];
            $like = Like::create($data);
            $recipient = User::find($post->user_id);
            event( new LikePostEvent( $post->id , $recipient));
            return  commonSuccessMessage("Post Liked Successfully", 200);
        }

        $delete_like = $like->delete();
        if($delete_like){
            return commonSuccessMessage("Post Unliked",200);
        }

    }


    public function postComments(PostCommentsRequest $request){
        $post_id = $request->post_id;

        $comments = Comment::with('user:id,full_name,avatar','nested_comments')->where('post_id',$post_id)->get();
        
        if($comments->count() == 0){
            return commonErrorMessage("No Comments Found",404);
        }
        
        return apiSuccessMessage("Comments", $comments);
    }

    public function getFollowingPosts()
    {
        $following_lists_ids = User::find(auth()->id())->following()->pluck('following_id');
        
        $posts = Post::select('posts.id','posts.user_id','posts.text','posts.file','posts.type','posts.created_at',
                    DB::raw('(select count(id) from likes where user_id = "'.auth()->id().'" AND post_id = posts.id) as is_like'),
                    )->withCount('likes','comments')
                    ->with('author')
                    ->whereIn('user_id',$following_lists_ids)
                    ->where('group_id',null)
                    ->latest()->get();
        
        if($posts->count() == 0)
        {
            return commonErrorMessage("No Following Posts Found",404);
        }
        
        return apiSuccessMessage("Following Posts list",FollowingPostsResource::collection($posts));
    }

 
    public function searchPosts(SearchPostRequest $request)
    {
        $search = $request->search;
        $following_lists_ids = User::find(auth()->id())->following()->pluck('following_id');
        // return $following_lists_ids;

        $search_posts = Post::select('posts.id','posts.user_id','posts.text','posts.file','posts.type','posts.created_at',
                            DB::raw('(select count(id) from likes where user_id = "'.auth()->id().'" AND post_id = posts.id) as is_like'),
                            )->withCount('likes','comments')
                            ->whereIn('posts.user_id',$following_lists_ids)
                            ->with('author')->where('text','LIKE', '%'.$search. '%')->orWhere(function($query) use($search){
                            $query->whereHas('author',function($q) use($search)
                            {
                                $q->where('full_name','LIKE', '%'.$search. '%');
                            });
                            })
                            
                            ->get();
        
        if ( $search_posts->count() == 0 )
        {
            return commonErrorMessage("No result Found",404);
        }
        
        
        return apiSuccessMessage("Search Result", FollowingPostsResource::collection($search_posts));
    }
}
