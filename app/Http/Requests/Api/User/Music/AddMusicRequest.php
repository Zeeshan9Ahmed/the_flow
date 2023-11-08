<?php

namespace App\Http\Requests\Api\User\Music;

use Illuminate\Foundation\Http\FormRequest;

class AddMusicRequest extends FormRequest
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
        return [
            'music_id' => 'required|unique:music',
            'music_image_url' => 'required',
            'music_url' => 'required',
            'artist_name' => 'required'
        ];
    }
}
