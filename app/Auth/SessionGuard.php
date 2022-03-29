<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use App\Http\Resources\AuthResource;
use App\Http\Resources\OnboardingResource;
use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\Auth\UserProvider;
use Symfony\Component\HttpFoundation\Request;

class SessionGuard extends \Illuminate\Auth\SessionGuard
{
    public function toResource($additional = [])
    {
        if (auth()->check()) {
            auth()->user()->load([
                'birth_place',
                'reports',
            ]);
        }

        // $stripeSubscriptionPrice = \Stripe\Price::retrieve(env('STRIPE_SUBSCRIPTION_PRICE_ID'));

        return new AuthResource(array_merge([
            'user' => auth()->user(),
            'email' => $this->session->get('auth.email'),
//            'stripe' => [
//                'public_key' => env('STRIPE_PUBLIC_KEY'),
//                'subscription_price' => [
//                    'currency' => Str::upper($stripeSubscriptionPrice->currency),
//                    'amount' => $stripeSubscriptionPrice->unit_amount / 100,
//                    'interval' => $stripeSubscriptionPrice->recurring->interval,
//                    'trial_days' => $stripeSubscriptionPrice->recurring->trial_period_days,
//                    'livemode' => $stripeSubscriptionPrice->livemode,
//                ],
//            ],
            'onboarding' => (new OnboardingResource(false))->toArray(request()),
        ], $additional));
    }
}
