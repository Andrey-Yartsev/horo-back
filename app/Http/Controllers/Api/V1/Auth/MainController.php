<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User\User;
use App\Auth\SessionGuard;
use App\Models\Geo\Locality;
use Illuminate\Http\Request;
use A3om\DataComposer\JsonResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\AuthResource;
use App\Http\Resources\UserResource;

class MainController extends Controller
{
    /**
     * Get Auth
     */
    public function get()
    {
        if (auth()->guard() instanceof SessionGuard) {
            return auth()->toResource();
        }

        return auth('web')->toResource();
    }

    /**
     * Log the user out of the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        if (!auth()->check()) {
            if (auth()->guard() instanceof SessionGuard) {
                return auth()->toResource();
            }

            return new AuthResource(null);
        }

        $authEmail = [
            'value' => auth()->user()->email,
            'user' => auth()->user(),
        ];

        if (auth()->guard() instanceof SessionGuard) {
            auth()->logout();
            $request->session()->put('auth.email', $authEmail);

            return auth()->toResource();
        }

        auth()->user()->currentAccessToken()->delete();

        return new AuthResource(null);
    }
}
