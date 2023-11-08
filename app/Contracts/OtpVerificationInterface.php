<?php
namespace App\Contracts;
interface OtpVerificationInterface {
    public function verify($request);
    public function resendCode($request);
    
}