<?php
namespace App\Services\User;
use App\Contracts\OtpVerificationInterface;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OtpVerificationService implements OtpVerificationInterface {
    public function verify($request){
        
        $user = User::where(['email' => $request->email])->first();
        
        // return $user;
        if(empty($user)){
            return commonErrorMessage("Sorry, No User found", 400);
        }

        if($request->verification_code != $user->verification_code){
            return commonErrorMessage("Invalid Verfication Code", 400);
        }
            
            
            
            if($request->type == 'password_reset'){
                $user->is_verified = '1';
                $user->verification_code = null;
                $user->save();
                return commonSuccessMessage("verification Completed");
            }

                $user->is_verified = '1';
                $user->verification_code = null;
                $user->save();
            
            $user->tokens()->delete();
            $token = $user->createToken('AuthToken')->plainTextToken;
            $profile = User::select('id','full_name','email','avatar','cover_image','date_of_birth','zip_code','state','address','is_active','is_profile_complete','is_verified',
                            DB::raw('(select count(id) from notifications where to_user_id = '.$user->id.' AND notification_is_read = "0") as notification_count '),
                        )
                        ->withCount('followers','following')
                        ->where('id',$user->id)
                        ->first();
            return apiSuccessMessage('Verification Completed', new UserResource($profile), $token);
                
    }
    
    public function resendCode($request){
        $type = $request->type;
        $user = User::where('email', $request->email)->first();
        $message = '';
        if(!$user){
            return commonErrorMessage("User Not Found", 400);
        }
            $user->verification_code = 123456;
            if($type == 'forgot_password'){
                $user->is_forgot = "1";
                $message = "Verification code for Forgot password has been sent on ur email";
                //send email for forgot password
            }
            if($type == 'resend_otp'){
                $message = "resend Verification code has been sent successfully";
                //send email for verification code
            }
            $user->save();
            $data = ['id' => $user->id];
            
            if($user){
                return apiSuccessMessage($message, $data);
            }else{
                return commonErrorMessage("Something Went Wrong while updating data");
            }
        
    }
}