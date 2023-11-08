<?php

namespace App\Http\Requests\Api\User\User;

use Illuminate\Foundation\Http\FormRequest;

class SendAttachmentRequest extends FormRequest
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
            // 'attachments.*' => 'required|mimes:jpeg,png,jpg,gif',
            'sender_id' => 'required',
            'reciever_id' => 'required',
            'chat_type' => 'required|in:image,video'
        ];
    }
}
