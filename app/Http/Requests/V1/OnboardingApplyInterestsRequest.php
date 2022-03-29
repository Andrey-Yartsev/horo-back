<?php

namespace App\Http\Requests\V1;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class OnboardingApplyInterestsRequest extends FormRequest
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
            'interests' => 'required|array|max:' . count(User::$interests),
            'interests.*' => 'required|string|in:' . implode(',', User::$interests),
        ];
    }
}
