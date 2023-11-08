<?php

namespace App\Providers;
use App\Contracts\CreateUserInterface;
use App\Contracts\OtpVerificationInterface;
use App\Contracts\UserLoginInterface;
use App\Contracts\UserPasswordInterface;
use App\Contracts\LevelInterface;
use App\Services\User\CreateUserService;
use App\Services\User\OtpVerificationService;
use App\Services\User\UserLoginService;
use App\Services\User\UserPasswordService;
use App\Services\Levels\LevelService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(CreateUserInterface::class, CreateUserService::class);
        $this->app->bind(OtpVerificationInterface::class, OtpVerificationService::class);
        $this->app->bind(UserLoginInterface::class, UserLoginService::class);
        $this->app->bind(UserPasswordInterface::class, UserPasswordService::class);
        $this->app->bind(LevelInterface::class, LevelService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
