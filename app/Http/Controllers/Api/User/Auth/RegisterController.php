<?php

namespace App\Http\Controllers\Api\User\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\User\Auth\CreateUserRequest;
use App\Http\Requests\Api\User\Auth\CompleteProfileRequest;
use App\Contracts\CreateUserInterface;


class RegisterController extends Controller
{
    private $user;
    public function __construct(CreateUserInterface $user){
        $this->user = $user;
    }
    public function createUser(CreateUserRequest $request){
        return $this->user->save($request);       
    }
    public function completeProfile(CompleteProfileRequest $request){
        return $this->user->completeProfile($request);
    }

}
