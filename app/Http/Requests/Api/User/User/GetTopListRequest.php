<?php

namespace App\Http\Requests\Api\User\User;

use Illuminate\Foundation\Http\FormRequest;

class GetTopListRequest extends FormRequest
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
            'user_id' => 'required|numeric',
            'type' => 'required|in:top_friends,top_followers' 
        ];
    }
}
