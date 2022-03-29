<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User;
use App\Auth\SessionGuard;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\EmailResource;
use App\Http\Requests\V1\AuthEmailCheckRequest;

class EmailController extends Controller
{
    public function check(AuthEmailCheckRequest $request)
    {
        $input = $request->validated();
        $authEmailValue = strtolower($input['user']['email']);
        $userQuery = User::query();
        $userQuery->where('email', $authEmailValue);
        $user = $userQuery->first();

        $authEmail = [
            'value' => $authEmailValue,
            'user' => $user,
        ];

        if (auth()->guard() instanceof SessionGuard) {
            $request->session()->put('auth.email', $authEmail);
        }

        return new EmailResource($authEmail);
    }

    public function forget(Request $request)
    {
        if (auth()->guard() instanceof SessionGuard) {
            $request->session()->forget('auth.email');
        }

        return ['data' => null];
    }
}
