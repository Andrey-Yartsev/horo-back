<?php

namespace App\Http\Requests\V1;

use App\Models\Place;
use Illuminate\Foundation\Http\FormRequest;

class OnboardingApplyBirthPlaceRequest extends FormRequest
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
        Place::getOrMakeByKey($this->input('birth_place_key'));

        return [
            'birth_place_key' => 'required|string|exists:places,key',
        ];
    }
}
