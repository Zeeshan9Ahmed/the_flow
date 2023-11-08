<?php

namespace App\Http\Requests\Api\User\User;

use Illuminate\Foundation\Http\FormRequest;

class MakeTopFriendOrFollowerRequest extends FormRequest
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
            'other_user_id' => 'required|numeric',
            'type' => 'required|in:friend,follower'
        ];
    }
}
