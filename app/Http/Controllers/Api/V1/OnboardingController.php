<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\OnboardingResource;
use App\Http\Requests\V1\OnboardingApplyNameRequest;
use App\Http\Requests\V1\OnboardingApplyGenderRequest;
use App\Http\Requests\V1\OnboardingApplyHandScanRequest;
use App\Http\Requests\V1\OnboardingApplyBirthDateRequest;
use App\Http\Requests\V1\OnboardingApplyBirthTimeRequest;
use App\Http\Requests\V1\OnboardingApplyInterestsRequest;
use App\Http\Requests\V1\OnboardingApplyBirthPlaceRequest;
use App\Http\Requests\V1\OnboardingApplyNotificationsRequest;
use App\Http\Requests\V1\OnboardingApplyRelationshipsRequest;
use Exception;

class OnboardingController extends Controller
{
    public function testSession(OnboardingApplyNameRequest $request)
    {
        //$request->session()->put('key', 'asd');
        //return $request->session()->get('key');
    }

    public function applyName(OnboardingApplyNameRequest $request)
    {
        return $request->session()->getId();
        $input = $request->validated();

        if ($user = auth()->user()) {
            $user->fill($input);
            $user->onboarding = ($user->onboarding === User::ONBOARDING_NAME ? User::ONBOARDING_GENDER : $user->onboarding);
            $user->save();
        } else {
            session()->put('onboarding.name', $input['name']);

            if (!session()->exists('onboarding.step') || session()->get('onboarding.step') === User::ONBOARDING_NAME) {
                session()->put('onboarding.step', User::ONBOARDING_GENDER);
            }
        }

        return new OnboardingResource();
    }

    public function applyGender(OnboardingApplyGenderRequest $request)
    {
        $input = $request->validated();

        if ($user = auth()->user()) {
            $user->fill($input);
            $user->onboarding = ($user->onboarding === User::ONBOARDING_GENDER ? User::ONBOARDING_BIRTH_DATE : $user->onboarding);
            $user->save();
        } else {
            session()->put('onboarding.gender', $input['gender']);
            if (session()->get('onboarding.step') === User::ONBOARDING_GENDER) {
                session()->put('onboarding.step', User::ONBOARDING_BIRTH_DATE);
            }
        }

        return new OnboardingResource();
    }

    public function applyBirthDate(OnboardingApplyBirthDateRequest $request)
    {
        $input = $request->validated();

        if ($user = auth()->user()) {
            $user->fill($input);
            $user->onboarding = ($user->onboarding === User::ONBOARDING_BIRTH_DATE ? User::ONBOARDING_BIRTH_TIME : $user->onboarding);
            $user->save();
        } else {
            session()->put('onboarding.birth_date', $input['birth_date']);

            if (session()->get('onboarding.step') === User::ONBOARDING_BIRTH_DATE) {
                session()->put('onboarding.step', User::ONBOARDING_BIRTH_TIME);
            }
        }

        return new OnboardingResource();
    }

    public function applyBirthTime(OnboardingApplyBirthTimeRequest $request)
    {
        $input = $request->validated();

        if ($user = auth()->user()) {
            $user->fill($input);
            $user->onboarding = ($user->onboarding === User::ONBOARDING_BIRTH_TIME ? User::ONBOARDING_BIRTH_PLACE : $user->onboarding);
            $user->save();
        } else {
            session()->put('onboarding.birth_time', $input['birth_time']);

            if (session()->get('onboarding.step') === User::ONBOARDING_BIRTH_TIME) {
                session()->put('onboarding.step', User::ONBOARDING_BIRTH_PLACE);
            }
        }

        return new OnboardingResource();
    }

    public function applyBirthPlace(OnboardingApplyBirthPlaceRequest $request)
    {
        $input = $request->validated();

        if ($user = auth()->user()) {
            $user->fill($input);
            $user->onboarding = ($user->onboarding === User::ONBOARDING_BIRTH_PLACE ? User::ONBOARDING_RELATIONSHIPS : $user->onboarding);
            $user->save();
        } else {
            session()->put('onboarding.birth_place_key', $input['birth_place_key']);

            if (session()->get('onboarding.step') === User::ONBOARDING_BIRTH_PLACE) {
                session()->put('onboarding.step', User::ONBOARDING_RELATIONSHIPS);
            }
        }

        return new OnboardingResource();
    }

    public function applyRelationships(OnboardingApplyRelationshipsRequest $request)
    {
        $input = $request->validated();

        if ($user = auth()->user()) {
            $user->fill($input);
            $user->onboarding = ($user->onboarding === User::ONBOARDING_RELATIONSHIPS ? User::ONBOARDING_INTERESTS : $user->onboarding);
            $user->save();
        } else {
            session()->put('onboarding.relationship_type', $input['relationship_type']);

            if (session()->get('onboarding.step') === User::ONBOARDING_RELATIONSHIPS) {
                session()->put('onboarding.step', User::ONBOARDING_INTERESTS);
            }
        }

        return new OnboardingResource();
    }

    public function applyInterests(OnboardingApplyInterestsRequest $request)
    {
        $input = $request->validated();

        if ($user = auth()->user()) {
            $user->fill($input);
            $user->onboarding = ($user->onboarding === User::ONBOARDING_INTERESTS ? User::ONBOARDING_NOTIFICATIONS : $user->onboarding);
            $user->save();
        } else {
            session()->put('onboarding.interests', $input['interests']);

            if (session()->get('onboarding.step') === User::ONBOARDING_INTERESTS) {
                session()->put('onboarding.step', User::ONBOARDING_NOTIFICATIONS);
            }
        }

        return new OnboardingResource();
    }

    public function applyNotifications(OnboardingApplyNotificationsRequest $request)
    {
        $input = $request->validated();

        if ($user = auth()->user()) {
            $user->fill($input);
            $user->onboarding = ($user->onboarding === User::ONBOARDING_NOTIFICATIONS ? null : $user->onboarding);
            $user->save();
        } else {
            session()->put('onboarding.receive_notifications_at', $input['receive_notifications_at']);

            if (session()->get('onboarding.step') === User::ONBOARDING_NOTIFICATIONS) {
                session()->put('onboarding.step', null);
            }
        }

        return new OnboardingResource();
    }
}
