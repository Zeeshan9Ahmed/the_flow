<?php

namespace App\Http\Controllers\Api\User\Auth;

use App\Http\Controllers\Controller;
use App\Contracts\UserPasswordInterface;
use App\Http\Requests\Api\User\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\User\Auth\UpdatePasswordRequest;
use App\Http\Requests\Api\User\Auth\ResetPasswordOtpVerifyRequest;
use App\Http\Requests\Api\User\Auth\ResetPasswordRequest;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    private $password;
    public function __construct(UserPasswordInterface $password){
        $this->password = $password;
    }

    public function forgotPassword(ForgotPasswordRequest $request){
        return $this->password->forgotPassword($request);
    }
    public function forgotPasswordOtpVerify(ResetPasswordOtpVerifyRequest $request){
        return $this->password->forgotPasswordOtpVerify($request);
    }
    public function resetForgotPassword(ResetPasswordRequest $request){
        return $this->password->resetForgotPassword($request);
    }
    public function changepassword(UpdatePasswordRequest $request){
        return $this->password->updatePassword($request);
    }

}
