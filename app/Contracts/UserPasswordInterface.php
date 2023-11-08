<?php
namespace App\Contracts;
interface UserPasswordInterface {
    public function forgotPassword($request);
    public function forgotPasswordOtpVerify($request);
    public function resetForgotPassword($request);
    public function updatePassword($request);
}