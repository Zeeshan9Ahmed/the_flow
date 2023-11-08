<?php
namespace App\Services\User;
use App\Contracts\UserLoginInterface;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserLoginService implements UserLoginInterface {
    public function login($request){
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = Auth::user();
            $user->device_type = $request->device_type;
            $user->device_token = $request->device_token;
            if($user->is_verified == 0)
            {
                $profile = User::select('id','full_name','email','avatar','cover_image','date_of_birth','zip_code','state','address','phone','is_active','is_profile_complete','is_verified','background_profile',
                            DB::raw('(select count(id) from notifications where to_user_id = '.auth()->id().' AND notification_is_read = "0") as notification_count '),
                        )
                        ->withCount('followers','following')
                        ->where('id',auth()->id())
                        ->first();
                
                $response = ['status' => 0, 'message' => "Your account is not verified, please verify your account", 'data' =>  (object) new UserResource($profile) ];
                return response()->json($response, 400);
                
            }
            
            $user->is_active = "1";
            $user->save();
            $profile = User::select('id','full_name','email','avatar','cover_image','date_of_birth','zip_code','state','address','phone','is_active','is_profile_complete','is_verified','background_profile',
                            DB::raw('(select count(id) from notifications where to_user_id = '.auth()->id().' AND notification_is_read = "0") as notification_count '),
                        )
                        ->withCount('followers','following')
                        ->where('id',auth()->id())
                        ->first();
                        
            
            $user->tokens()->delete();
            $token = $user->createToken('AuthToken')->plainTextToken;
            return apiSuccessMessage("User Login Successfully", new UserResource($profile), $token);

        }else{
            return commonErrorMessage("Invalid Credientials",400);
        }
    }

    public function socialLogin($request){
    
        $user = User::where('social_token',$request->social_token)->first();
        
        if(! empty($user)){
            $user_id = $user->id;
            $avatar = null;
            if($request->hasFile('image')){
                $imageName = time().'.'.$request->image->getClientOriginalExtension();
                $request->image->move(public_path('/uploadedimages'), $imageName);
                $avatar = asset('public/uploadedimages')."/".$imageName;
            }
            $user->social_token = $request->social_token;
            $user->social_type = $request->social_type;
            $user->device_token = $request->device_token;
            $user->device_type = $request->device_type;
            $user->avatar = $avatar;
            $user->save();
            if($user){

                $user = User::withCount(['notifications' => function($query){
                    return $query->where('seen','0');
                }])->where('id', $user_id)->first();

                $user->tokens()->delete();
                $token = $user->createToken('AuthToken')->plainTextToken;
            
                return apiSuccessMessage("Social Login Successfully", $user, $token);
            }else{
                return commonErrorMessage("Something Went Wrong while Updating data" ,400);
            }
        }else{
            // $check = User::where('email',$request->email)->first();
            // if(! empty($check)){
            //     return commonErrorMessage("Email is already taken",400);
            // }
            $avatar = null;
            if($request->hasFile('image')){
                $imageName = time().'.'.$request->image->getClientOriginalExtension();
                $request->image->move(public_path('/uploadedimages'), $imageName);
                $avatar = asset('public/uploadedimages')."/".$imageName;
            }
            $arr = [
                'name' => $request->name,
                'email' => null,
                'avatar' => $avatar,
                'device_type' => $request->device_type,
                'device_token' => $request->device_token,
                'social_type' => $request->social_type,
                'social_token' => $request->social_token,
                'is_profile_complete' => "0",
                'is_verified' => "1",
                'is_blocked' => "0"
            ];

            $user = User::create($arr);
            $token = $user->createToken('AuthToken')->plainTextToken;
            return apiSuccessMessage("Social Login Successfully", $user, $token);
        }
    }

    public function logout(){
        if(Auth::check()){
            $user = Auth::user();
            // return $user;
            $user->currentAccessToken()->delete();
            $user->is_active = "0";
            $user->save();
            return commonSuccessMessage("User LogOut Successfully");
        }else{
            return commonErrorMessage("User Is not Authenticated");
        }
    }
}