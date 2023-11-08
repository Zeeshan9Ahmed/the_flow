<?php

namespace App\Http\Controllers\Api\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Auth\UserVerificationRequest;
use App\Contracts\OtpVerificationInterface;
use App\Http\Requests\Api\User\Auth\ResendCodeRequest;

class VerificationController extends Controller
{
    private $otp;
    public function __construct(OtpVerificationInterface $otp){
        $this->otp = $otp;
    }
    public function verifyUser(UserVerificationRequest $request){
        return $this->otp->verify($request);
    }

    public function resendVerificationCode(ResendCodeRequest $request){
        $resendCode = $this->otp->resendCode($request);
        return $resendCode;
    }
}
