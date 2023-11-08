<?php
namespace App\Services\User;
use App\Contracts\CreateUserInterface;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CreateUserService implements CreateUserInterface {
    public function save($request){
        //mt_rand(100000,900000)
        $avatar = null;
        if($request->hasFile('image')){
            $imageName = time().'.'.$request->image->getClientOriginalExtension();
            $request->image->move(public_path('/uploadedimages'), $imageName);
            $avatar = asset('public/uploadedimages')."/".$imageName;
        }
        // return $avatar;
        $arr = [
            // 'full_name' => $request->full_name,
            'email' => $request->email,
            
            'password' => bcrypt($request->password),
            'device_type' => $request->device_type,
            'device_token' => $request->device_token,
            // 'address' => $request->address,
            // 'zip_code' => $request->zip_code,
            // 'state' => $request->state,
            'is_forgot' => "0",
            'is_verified' => "0",
            'verification_code' => 123456,
            'is_blocked' => "0"
        ];
        // return $arr;
        $data = ['id' => User::create($arr)->id];
        return apiSuccessMessage("User Created Successfully" ,$data);
    }  
    
    public function completeProfile($request){
        $user = Auth::user();
        
            $user->full_name = $request->full_name;
            if($request->hasFile('image')){
                $imageName = time().'profile'.'.'.$request->image->getClientOriginalExtension();
                $request->image->move(public_path('/uploadedimages'), $imageName);
                $user->avatar = asset('public/uploadedimages')."/".$imageName;
            }

            if($request->hasFile('cover_image')){
                $imageName = time().'cover'.'.'.$request->cover_image->getClientOriginalExtension();
                $request->cover_image->move(public_path('/uploadedimages'), $imageName);
                $user->cover_image = asset('public/uploadedimages')."/".$imageName;
            }

            if($request->hasFile('background_profile')){
                $imageName = time().'background'.'.'.$request->background_profile->getClientOriginalExtension();
                $request->background_profile->move(public_path('/uploadedimages'), $imageName);
                $user->background_profile = asset('public/uploadedimages')."/".$imageName;
            }

            $user->phone = $request->phone;
            $user->address = $request->address;
            $user->zip_code = $request->zip_code;
            $user->state = $request->state;
            $user->is_profile_complete = "1";
            $user->date_of_birth = $request->date_of_birth;
            if($user->save()){
                $profile = User::select('id','full_name','email','phone','avatar','cover_image','date_of_birth','zip_code','state','address','is_active','is_profile_complete','is_verified','background_profile')
                            ->withCount('followers','following')
                            ->where('id',auth()->id())
                            ->first();
                
                return apiSuccessMessage("User Profile Completed Successfully", new UserResource($profile));
            }else{
                return commonErrorMessage("Something went Wrong While updating the data", 400);
            }
        
    }
    
}