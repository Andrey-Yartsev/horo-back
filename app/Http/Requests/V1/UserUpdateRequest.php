<?php

namespace App\Http\Requests\V1;

use App\Models\Place;
use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->id === $this->route('user')->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Place::getOrMakeByKey($this->input('user.birth_place_key'));

        return [
            'user.name' => 'string',
            'user.receive_notifications_at' => 'date_format:H:i:s|nullable',
            'user.birth_date' => 'date_format:Y-m-d',
            'user.birth_time' => 'date_format:H:i:s|nullable',
            'user.birth_place_key' => 'string|exists:places,key',
        ];
    }
}
