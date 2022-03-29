<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User;
use App\Auth\SessionGuard;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $input;
    protected $user;

    /**
     * Handle a login request to the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $userQuery = User::query();
        $userQuery->where('email', $request->input('user.email'));
        $this->user = $userQuery->first();

        if ($this->user && $this->user->auth_method !== User::AUTH_METHOD_EMAIL) {
            abort(400, 'Use Different Auth Method');
        }

        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') && $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Validate the user login request.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $this->input = $request->validate(array_merge([
            'user' => 'required|array',
            'user.email' => 'required|string|exists:users,email',
        ], $this->user && $this->user->has_password ? [
            'user.password' => 'required|string',
        ] : [], [
            'remember_me' => 'boolean',
        ], auth()->guard() instanceof SessionGuard ? [] : [
            'application_name' => 'required|string',
        ]), [
            // 'user.password.required' => ':)',
        ]);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        if (!$this->user) {
            return false;
        }

        if ($this->user->has_password && !$this->user->doesPasswordEqual($this->input['user']['password'])) {
            return false;
        }

        if (auth()->guard() instanceof SessionGuard) {
            auth('web')->login($this->user, $this->input['remember_me'] ?? true);
        } else {
            auth()->setUser($this->user);
        }

        if (session()->exists('onboarding.step')) {
            $this->user->fillFromOnboarding();
            $this->user->save();
            session()->forget('onboarding.step');
        }

        return true;
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @return UserResource
     */
    protected function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);

        if (auth()->guard() instanceof SessionGuard) {
            return auth()->toResource();
        }

        $token = auth()->user()->createToken($this->input['application_name']);

        return auth('web')->toResource(['token' => $token->plainTextToken]);
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            'user.password' => [trans('auth.password')],
        ]);
    }
}
