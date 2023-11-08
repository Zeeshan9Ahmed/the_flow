<?php

namespace App\Http\Requests\Api\User\Friends;

use Illuminate\Foundation\Http\FormRequest;

class AcceptFriendRequestRequest extends FormRequest
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
        // dd($this);
        return [
            'recipient_id' => 'required|numeric'
        ];
    }
}
