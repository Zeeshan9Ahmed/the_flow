<?php


use App\Models\Genere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\Music\MusicController;
use App\Http\Controllers\Api\User\Music\GenereController;
use App\Http\Controllers\Api\User\Event\EventController;
use App\Http\Controllers\Api\User\Friends\FriendController;
use App\Http\Controllers\Api\User\Group\GroupController;
use App\Http\Controllers\Api\User\Live\LiveController;
use App\Http\Controllers\Api\User\Post\PostController;
use App\Http\Controllers\Api\User\User\UserController;
use Illuminate\Support\Facades\Http;
 

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::group(['namespace' => 'App\Http\Controllers\Api\User\Auth'],function(){
    Route::post('user/signup','RegisterController@createUser');
    Route::post('user/otp-verification','VerificationController@verifyUser');
    Route::post('user/resend-otp','VerificationController@resendVerificationCode');
    Route::post('user/login','LoginController@login');
    Route::post('user/social-login','LoginController@socialLogin');
    Route::post('user/forgot-password', 'ResetPasswordController@forgotPassword');
    // Route::post('user/forgot-password-otp-verify', 'ResetPasswordController@forgotPasswordOtpVerify');
    Route::post('user/reset-forgot-password', 'ResetPasswordController@resetForgotPassword');
    
    Route::group(['middleware'=>'auth:sanctum'],function(){
        Route::post('user/change-password', 'ResetPasswordController@changepassword');
        Route::post('user/update-profile', 'RegisterController@completeProfile');
        Route::post('user/logout','LoginController@logout');
        Route::delete('user/delete-account','LoginController@delete_account');
        
        
        //Core Module
        Route::get('genere-list',[GenereController::class, 'genereList']);
        Route::post('assign-genere',[GenereController::class, 'assignGenereToUser']);


        Route::post('create-event',[EventController::class, 'createEvent']);
        Route::get('events',[EventController::class, 'getAllEvents']);
        Route::get('search-event',[EventController::class, 'searchEvent']);
        Route::get('event-detail',[EventController::class, 'eventDetail']);
        Route::get('event-invite-list',[EventController::class, 'eventInviteList']);
        Route::post('invite-in-event',[EventController::class, 'inviteInEvent']);
        
        // 
        Route::post('create-post',[PostController::class, 'createPost']);
        Route::post('edit-post',[PostController::class, 'editPost']);
        Route::post('delete-post',[PostController::class, 'deletePost']);
        //editPost
        Route::post('comment-on-post',[PostController::class, 'commentOnPost']);
        Route::post('like-unlike-post',[PostController::class, 'likeUnlikePost']);
        Route::get('post-comments',[PostController::class, 'postComments']);
        Route::get('get-following-posts',[PostController::class, 'getFollowingPosts']);
        Route::get('search-posts',[PostController::class, 'searchPosts']);
        // search-posts
        Route::post('create-group',[GroupController::class, 'createGroup']);
        Route::get('invite-members-list',[GroupController::class, 'inviteMembersList']);
        Route::post('invite-in-group',[GroupController::class, 'inviteInGroup']);
        Route::post('accept-or-reject-group-request',[GroupController::class, 'acceptOrRejectGroupRequest']);
        Route::post('leave-group',[GroupController::class, 'leaveGroup']);
        Route::post('delete-group',[GroupController::class, 'deleteGroup']);
        Route::get('groups',[GroupController::class, 'AuthGroups']);
        Route::get('group-wise-posts',[GroupController::class, 'getGroupWisePosts']);
        Route::get('get-group-members',[GroupController::class, 'getGroupMembers']);
        Route::get('get-community-posts',[GroupController::class, 'getCommunityPosts']);
        Route::get('search-community',[GroupController::class, 'searchCommunityPosts']);
        Route::get('group-detail',[GroupController::class, 'groupDetail']);
        
        Route::get('notifications',[UserController::class, 'notifications']);
        Route::get('my-profile',[UserController::class, 'myProfile']);
        Route::get('profile-page-data',[UserController::class, 'profilePageData']);
        Route::post('follow-unfollow-user',[UserController::class, 'followUnFollowUser']);
        Route::get('get-followers',[UserController::class, 'getFollowersList']);
        Route::get('get-following',[UserController::class, 'getFollowingList']);
        Route::get('get-user-profile',[UserController::class, 'getUserProfile']);
        Route::post('make-top',[UserController::class, 'makeTop']);
        Route::get('get-top-list',[UserController::class, 'getTopList']);
        Route::get('chat-list',[UserController::class, 'chatList']);
        Route::post('send-attachment',[UserController::class, 'sendAttachment']);
        
        Route::post('send-request',[FriendController::class, 'sendRequest']);
        Route::post('accept-request',[FriendController::class, 'acceptRequest']);
        Route::post('cancel-request',[FriendController::class, 'cancelRequest']);
        Route::post('reject-request',[FriendController::class, 'rejectRequest']);
        Route::post('unfriend',[FriendController::class, 'unFriend']);
        Route::get('friend-list',[FriendController::class, 'friendList']);
        Route::get('search-user',[FriendController::class, 'searchUser']);

        Route::post('add-music',[GenereController::class,'addMusic']);
        Route::get('get-music-ids',[GenereController::class,'getMusicIds']);

        Route::post('end-stream', [LiveController::class,'sendNotificationToEndLiveStream']);
        
        Route::post('end-call', [LiveController::class,'sendNotificationToEndCall']);

    });
});
