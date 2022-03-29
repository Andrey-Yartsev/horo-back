<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\User\UserSet0;
use Illuminate\Validation\Rule;
use Illuminate\Auth\SessionGuard;
use App\Http\Controllers\Controller;
use App\Http\Resources\AuthResource;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Handle a registration request for the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $user = User::where('email', $request->input('user.email'))->first();

        if ($user) {
            if ($user && $user->auth_method !== User::AUTH_METHOD_EMAIL) {
                abort(400, 'Use Different Auth Method');
            }

            if ($user->password && !$user->doesPasswordEqual($request->input('user.password'))) {
                abort(400, 'Already Registered');
            }
        }

        $input = $request->validate(array_merge([
            'user' => 'required|array',
            'user.email' => 'required|email|unique:users,email,' . ($user->id ?? 0),
            'user.password' => 'string|min:8|nullable',
            'user.locale' => 'string|in:' . implode(',', config('app.allowed_locales')),
            'user.utc_offset' => 'required|integer',
            'remember_me' => 'boolean',
        ], auth()->guard() instanceof SessionGuard ? [] : [
            'application_name' => 'required|string',
        ]));

        if ($user) {
            $user->fill($input['user']);
            $user->save();
        } else {
            $user = User::create(array_merge($input['user'], [
                'onboarding' => User::ONBOARDING_NAME,
                'auth_method' => User::AUTH_METHOD_EMAIL,
            ]));
        }

        if (session()->exists('onboarding.step')) {
            $user->fillFromOnboarding();
            $user->save();
            session()->forget('onboarding.step');
        }

        if (auth()->guard() instanceof SessionGuard) {
            auth()->login($user, $input['remember_me'] ?? true);
            return auth()->toResource();
        }

        auth()->setUser($user);
        $token = auth()->user()->createToken($input['application_name']);

        return auth('web')->toResource(['token' => $token->plainTextToken]);
    }
}
