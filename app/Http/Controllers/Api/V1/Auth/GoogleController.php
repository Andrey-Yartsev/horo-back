<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User;
use App\Auth\SessionGuard;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Requests\V1\GoogleLoginRequest;

class GoogleController extends Controller
{
    public function login(GoogleLoginRequest $request)
    {
        $input = $request->validated();
        $googleClient = new \Google\Client();
        $googleClient->setApplicationName(config('app.name'));
        $googleClient->setAuthConfig(config('google.web'));
        $googleClient->setAccessType('offline');
        $googleClient->setIncludeGrantedScopes(true);
        $googleClient->addScope('email');
        $googleClient->addScope('profile');
        $googleClient->setPrompt('select_account consent');
        $googleClient->setRedirectUri('postmessage');
        $googleAccessToken = $googleClient->fetchAccessTokenWithAuthCode($input['code']);
        $googleServiceOauth2 = new \Google\Service\Oauth2($googleClient);
        $googleUser = $googleServiceOauth2->userinfo_v2_me->get();
        $user = User::where('email', $googleUser->email)->first();
        $wasUserCreated = false;

        if ($user) {
            if ($user->auth_method !== User::AUTH_METHOD_GOOGLE) {
                abort(400, 'User Has Different Auth Method');
            }
        } else {
            $user = User::create([
                'auth_method' => User::AUTH_METHOD_GOOGLE,
                'onboarding' => User::ONBOARDING_NAME,
                'email' => $googleUser->email,
                'name' => $googleUser->name,
                'locale' => $googleUser->locale,
                'utc_offset' => $input['user']['utc_offset'],
            ]);

            $wasUserCreated = true;
        }

        if (session()->exists('onboarding.step')) {
            $user->fillFromOnboarding();
            $user->save();
            session()->forget('onboarding.step');
        }

        if (auth()->guard() instanceof SessionGuard) {
            auth('web')->login($user, $input['remember_me'] ?? true);
        } else {
            auth()->setUser($user);
        }

        if (auth()->guard() instanceof SessionGuard) {
            return auth()->toResource(['is_new' => $wasUserCreated]);
        }

        $token = auth()->user()->createToken($input['application_name']);

        return auth('web')->toResource(['token' => $token->plainTextToken, 'is_new' => $wasUserCreated]);
    }
}
