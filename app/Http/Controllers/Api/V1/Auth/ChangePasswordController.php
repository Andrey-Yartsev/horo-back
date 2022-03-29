<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User;
use App\Auth\SessionGuard;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChangePasswordController extends Controller
{
    /**
     * Change User password
     *
     * @param Request $request
     * @return mixed
     */
    public function change(Request $request)
    {
        $user = auth()->user();

        if ($user->auth_method !== User::AUTH_METHOD_EMAIL) {
            abort(400, 'Bad Request');
        }

        $input = $request->validate(array_merge([
            'user' => 'required|array',
            'user.password' => 'required|string',
        ], auth()->guard() instanceof SessionGuard ? [] : [
            'application_name' => 'required|string',
        ]));

        $user->password = $input['user']['password'];
        $user->save();
        $user->tokens()->delete();

        if (auth()->guard() instanceof SessionGuard) {
            // TODO: if there is remember token in cookies we need to login user with remember me `true`
            auth()->login($user, true);
            $request->session()->regenerate();

            return auth()->toResource();
        }

        $token = $user->createToken($input['application_name']);

        return auth('web')->toResource(['token' => $token->plainTextToken]);
    }
}
