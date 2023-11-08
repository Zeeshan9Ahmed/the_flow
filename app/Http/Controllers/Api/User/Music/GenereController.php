<?php

namespace App\Http\Controllers\Api\User\Music;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Music\AddMusicRequest;
use App\Http\Requests\Api\User\Music\AssignGenereRequest;
use App\Http\Resources\UserResource;
use App\Models\Genere;
use App\Models\Music;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GenereController extends Controller
{
    public function genereList(){
        $generes = Genere::all();
        if(! $generes){
            return commonErrorMessage("No Generes Found", 400);
        }
        return apiSuccessMessage("Generes List", $generes);
    }

    public function assignGenereToUser(AssignGenereRequest $request){
        $user = Auth::user();
        $user->genere_id = $request->genere_id;
        $user->save();

        $profile = User::select('id','full_name','email','avatar','cover_image','date_of_birth','zip_code','state','address','is_active','is_profile_complete','is_verified',
                            DB::raw('(select count(id) from notifications where to_user_id = '.auth()->id().' AND notification_is_read = "0") as notification_count '),
                        )
                        ->withCount('followers','following')
                        ->where('id',auth()->id())
                        ->first();
                        
        return apiSuccessMessage("Genere Assigned Successfully", new UserResource($profile) );
    }

    public function addMusic(AddMusicRequest $request)
    {
        $data = [
            'user_id' => auth()->id(),
            'music_id' => $request->music_id,
            'music_image_url' => $request->music_image_url,
            'music_url' => $request->music_url,
            'artist_name' => $request->artist_name
        ];

        $music = Music::create($data);

        return apiSuccessMessage("Music Added", $music);
    }

    public function getMusicIds(Request $request)
    {
        $music_lists = Music::select('id','music_id','music_image_url','artist_name','music_url')->where('user_id', $request->user_id)->get();
        
        if ( $music_lists->count()  == 0)
        {
            return commonErrorMessage("No Music List Found");
        }
        
        return response()->json([
            'status' => 1,
            'message' => 'Music Ids',
            'data' => $music_lists
        ], 200);
    }
}
