<?php

namespace App\Http\Requests\Api\User\Post;

use Illuminate\Foundation\Http\FormRequest;

class CreatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        
        if($this->type == 'video'){
            return [
                'text' => 'required',
                'file' => 'required|mimes:mp4,ogx,oga,ogv,ogg,webm',
                'group_id' => 'sometimes|required|numeric',
            ];
            
        }elseif($this->type == 'image'){
            return [
                'text' => 'required',
                'file' => 'required|mimes:jpeg,png,jpg,gif',
                'group_id' => 'sometimes|required|numeric',
            ]; 
        }elseif($this->type == null){
            
            return [
                'text' => 'required',
                // 'file' => 'sometimes|required|mimes:jpeg,png,jpg,gif',
                'group_id' => 'sometimes|required|numeric',
            ];
            
        }

        return [
            'type' => 'in:video,image'
        ];
        
        
    }
}
