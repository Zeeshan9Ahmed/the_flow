<?php

namespace App\Providers;

use App\Events\AcceptFriendRequestEvent;
use App\Events\AcceptGroupRequestEvent;
use App\Events\CommentOnPostEvent;
use App\Events\FollowUserEvent;
use App\Events\InviteInEventEvent;
use App\Events\InviteInGroupEvent;
use App\Events\LikePostEvent;
use App\Events\RejectFriendRequestEvent;
use App\Events\SendFriendRequestEvent;
use App\Listeners\AcceptFriendRequestListener;
use App\Listeners\AcceptGroupRequestListener;
use App\Listeners\CommentOnPostListener;
use App\Listeners\FollowUserListener;
use App\Listeners\InviteInEventListener;
use App\Listeners\InviteInGroupListener;
use App\Listeners\LikePostEventListener;
use App\Listeners\RejectFriendRequestListener;
use App\Listeners\SendFriendRequestListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        SendFriendRequestEvent::class => [
            SendFriendRequestListener::class,
        ],
        AcceptFriendRequestEvent::class => [
            AcceptFriendRequestListener::class,
        ],
        RejectFriendRequestEvent::class => [
            RejectFriendRequestListener::class,
        ],
        FollowUserEvent::class => [
            FollowUserListener::class
        ],
        LikePostEvent::class => [
            LikePostEventListener::class
        ],
        CommentOnPostEvent::class => [
            CommentOnPostListener::class
        ],
        InviteInGroupEvent::class => [
            InviteInGroupListener::class
        ],
        InviteInEventEvent::class => [
            InviteInEventListener::class
        ],
        AcceptGroupRequestEvent::class => [
            AcceptGroupRequestListener::class
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
