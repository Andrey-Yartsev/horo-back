<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Models\Place;
use Illuminate\Http\Resources\Json\JsonResource;

class OnboardingResource extends JsonResource
{
    public $withUser = false;

    public function __construct($withUser = true)
    {
        $this->withUser = $withUser;
        parent::__construct(null);
    }

    public function toArray($request)
    {
        if ($user = auth()->user()) {
            $step = $user->onboarding;
            $index = $step ? array_search($step, User::$onboardings) : null;
            $number = $step ? $index + 1 : null;

            return array_merge([
                'step' => $step,
                'total' => count(User::$onboardings),
                'index' => $index,
                'number' => $number,
                'name' => $user->name,
                'gender' => $user->gender,
                'birth_date' => $user->birth_date,
                'birth_time' => $user->birth_time,
                'birth_place_key' => $user->birth_place_key,
                'birth_place' => Place::getOrMakeByKey($user->birth_place_key),
                'relationship_type' => $user->relationship_type,
                'interests' => $user->interests,
                'receive_notifications_at' => $user->receive_notifications_at,
            ], $this->withUser ? [
                'user' => new UserResource(auth()->user()->load([
                    'birth_place',
                    'reports',
                ])),
            ] : []);
        }

        $step = session()->exists('onboarding.step') ? session()->get('onboarding.step') : User::ONBOARDING_NAME;
        $index = $step ? array_search($step, User::$onboardings) : null;
        $number = $step ? $index + 1 : null;

        return array_merge([
            'step' => $step,
            'total' => count(User::$onboardings),
            'index' => $index,
            'number' => $number,
            'name' => session()->get('onboarding.name'),
            'gender' => session()->get('onboarding.gender'),
            'birth_date' => session()->get('onboarding.birth_date'),
            'birth_time' => session()->get('onboarding.birth_time'),
            'birth_place_key' => session()->get('onboarding.birth_place_key'),
            'birth_place' => Place::getOrMakeByKey(session()->get('onboarding.birth_place_key')),
            'relationship_type' => session()->get('onboarding.relationship_type'),
            'interests' => session()->get('onboarding.interests'),
            'receive_notifications_at' => session()->get('onboarding.receive_notifications_at'),
        ], $this->withUser ? ['user' => null] : []);
    }
}
