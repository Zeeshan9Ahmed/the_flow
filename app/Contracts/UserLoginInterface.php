<?php
namespace App\Contracts;
interface UserLoginInterface{
    public function login($request);
    public function socialLogin($request);
    public function logout();
}