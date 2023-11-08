<?php
namespace App\Services\User;
use App\Contracts\UserPasswordInterface;
use App\Models\User;
use Hash;
use Illuminate\Support\Facades\Auth;

class UserPasswordService implements UserPasswordInterface{
    public function forgotPassword($request){
        $user = User::where('email', $request->email)->first();
        if(empty($user)){
            return commonErrorMessage("User Not Found", 400);
        }else{
            // send email for verification code
            $user->verification_code = 123456;
            $user->is_forgot = "1";
            $user->save();
            $data = ['id' => $user->id];
            if($user){
                return apiSuccessMessage("Verification code for Forgot password has been sent on ur email", $data);
            }else{
                return commonErrorMessage("Something Went Wrong while updating data");
            }
        }
        
    }
    public function forgotPasswordOtpVerify($request){
        $verifyOtp = User::where(['email' => $request->email, 'verification_code' => $request->verification_code])->first();
        if( empty($verifyOtp) ){
            return commonErrorMessage("Invalid Credientials ", 400);
        }else{
            return commonSuccessMessage("Otp Verified");
        }
        
    }
    public function resetForgotPassword($request){
        $user = User::where('email', $request->email)->first();
        if( empty($user) ){
            return commonErrorMessage("User Not Found");
        }else{
            if($user->verification_code == $request->verification_code){
                $user->verification_code = null;
                $user->password = bcrypt($request->new_password);
                $user->is_forgot = "0";
                $user->save();
                $user->tokens()->delete();
                $token = $user->createToken('AuthToken')->plainTextToken;
                return apiSuccessMessage("Password reset Successfully", $user, $token);
            }else{
                return commonErrorMessage("Incorrect Verification code");
            }
        }
        
    }
    public function updatePassword($request){
        $user = User::where('id', Auth::user()->id)->first();
        if (Hash::check($request->old_password , $user->password)){
            if (Hash::check($request->new_password , $user->password)){
                return commonErrorMessage("New Password can not be match to Old Password",400);
            } 
            
            $user->password = bcrypt($request->new_password);
            $user->save();
            if($user){
                return commonSuccessMessage("Password Updated Successfully");
            }else{
                return commonErrorMessage("Something went wrong while updating old password", 400);
            } 
        }else{
            return commonErrorMessage("InCorrect Old password , please try again",400);
        }
        
    }
}