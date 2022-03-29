<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\V1\UserUpdateRequest;
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\V1\UserReportCreateRequest;
use App\Http\Requests\V1\UserGetReportPriceRequest;
use App\Http\Requests\V1\UserReportInitiateRequest;
use App\Http\Requests\V1\UserReportValidateRequest;
use App\Http\Requests\V1\UserUpdatePasswordRequest;
use App\Http\Requests\V1\UserSubscriptionCreateRequest;
use App\Http\Requests\V1\UserSubscriptionInitiateRequest;
use App\Http\Requests\V1\UserSubscriptionValidateRequest;
use App\Http\Requests\UserSubscriptionWantsToCancelRequest;
use App\Http\Requests\V1\UserUnlockFullAccessClosedRequest;
use App\Notifications\UserWantsToUnsubscribeNotification;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserController extends Controller
{
    public function unlockFullAccessClosed(UserUnlockFullAccessClosedRequest $request, User $user)
    {
        $input = $request->validated();
        $user->fill($input['user'] ?? []);
        $user->unlock_full_access_closed_at = now();
        $user->save();

        return new UserResource($user);
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        $input = $request->validated();
        $user->fill($input['user'] ?? []);
        $user->save();
        $user->wasChanged('birth_place_key') && $user->load(['birth_place']);

        return new UserResource($user);
    }

    public function initiateSubscription(UserSubscriptionInitiateRequest $request, User $user)
    {
        $input = $request->validated();

        if ($user->stripe_customer_id) {
            $user->syncSubscriptions();
            $user->cancelDuplicateSubscriptions();
        }

        $hasUserActiveSubscription = $user->has_active_subscription;
        $stripeCheckoutSession = null;

        if (!$hasUserActiveSubscription) {
            $stripeSubscriptionPrice = \Stripe\Price::retrieve(env('STRIPE_SUBSCRIPTION_PRICE_ID'));

            $stripeCheckoutSession = \Stripe\Checkout\Session::create([
                'success_url' => str_replace('CHECKOUT_SESSION_ID', '{CHECKOUT_SESSION_ID}', $input['success_url']),
                'cancel_url' => $input['cancel_url'],
                'customer' => $user->createOrGetStripeCustomer()->id,
                'payment_method_types' => ['card'],
                'mode' => 'subscription',
                'line_items' => [
                    ['price' => env('STRIPE_SUBSCRIPTION_PRICE_ID'), 'quantity' => 1],
                ],
                'subscription_data' => [
                    'trial_period_days' => $stripeSubscriptionPrice->recurring->trial_period_days,
                ],
            ]);
        }

        return [
            'data' => [
                'checkout_url' => $stripeCheckoutSession->url ?? null,
                'has_active_subscription' => $hasUserActiveSubscription,
            ],
        ];
    }

    public function validateSubscription(UserSubscriptionValidateRequest $request, User $user)
    {
        $input = $request->validated();
        $stripeCheckoutSession = \Stripe\Checkout\Session::retrieve($input['stripe_checkout_session_id']);
        $stripeCustomer = $user->createOrGetStripeCustomer();

        if ($stripeCheckoutSession->customer !== $stripeCustomer->id) {
            throw new BadRequestHttpException('Wrong Stripe Customer');
        }

        if (!$stripeCheckoutSession->subscription) {
            throw new BadRequestHttpException('No Stripe Subscription');
        }

        $user->syncSubscriptions();
        $user->cancelDuplicateSubscriptions();

        return new UserResource($user);
    }

    public function createSubscription(UserSubscriptionCreateRequest $request, User $user)
    {
        $input = $request->validated();

        if ($user->stripe_customer_id) {
            $user->syncSubscriptions();
            $user->cancelDuplicateSubscriptions();
        }

        $hasUserActiveSubscription = $user->has_active_subscription;
        $stripeCustomer = $user->createOrGetStripeCustomer();
        $user->updateStripeCustomerDefaultPaymentMethod($input['stripe_payment_method_id']);

        if (!$hasUserActiveSubscription) {
            $stripeSubscriptionPrice = \Stripe\Price::retrieve(env('STRIPE_SUBSCRIPTION_PRICE_ID'));

            \Stripe\Subscription::create([
                'customer' => $stripeCustomer->id,
                'items' => [
                    ['price' => env('STRIPE_SUBSCRIPTION_PRICE_ID'), 'quantity' => 1],
                ],
                'trial_period_days' => $stripeSubscriptionPrice->recurring->trial_period_days,
            ]);

            $user->syncSubscriptions();
            $user->cancelDuplicateSubscriptions();

            return [
                'data' => [
                    'has_been_subscribed' => $user->has_active_subscription,
                    'has_active_subscription' => true,
                ],
            ];
        }

        return [
            'data' => [
                'has_been_subscribed' => false,
                'has_active_subscription' => true,
            ],
        ];
    }

    public function wantToCancelSubscription(UserSubscriptionWantsToCancelRequest $request, User $user)
    {
        $input = $request->validated();
        $user->email_to_cancel_subscription = $input['email'];
        $user->save();

        Notification::route('mail', env('SUPPORT_EMAIL', 'a3om77@gmail.com'))
            ->notify(new UserWantsToUnsubscribeNotification($user));

        return ['data' => true];
    }

    public function getReportPrice(UserGetReportPriceRequest $request, User $user, $reportType)
    {
        return [];
        if (!in_array($reportType, ['natal-chart', 'synastry-report', 'transit-report'])) {
            abort(400, 'Bad Request');
        }

        $stripeReportPrice = \Stripe\Price::retrieve(
            env('STRIPE_REPORT_' . Str::replace('-', '_', Str::upper($reportType)) . '_PRICE_ID')
        );

        return [
            'data' => [
                'currency' => Str::upper($stripeReportPrice->currency),
                'amount' => $stripeReportPrice->unit_amount / 100,
                'livemode' => $stripeReportPrice->livemode,
            ],
        ];
    }

    public function initiateReport(UserReportInitiateRequest $request, User $user, $reportType)
    {
        if (!in_array($reportType, ['natal-chart', 'synastry-report', 'transit-report'])) {
            abort(400, 'Bad Request');
        }

        $input = $request->validated();

        $stripeReportPriceIds = [
            'natal-chart' => env('STRIPE_REPORT_NATAL_CHART_PRICE_ID'),
            'synastry-report' => env('STRIPE_REPORT_SYNASTRY_REPORT_PRICE_ID'),
            'transit-report' => env('STRIPE_REPORT_TRANSIT_REPORT_PRICE_ID'),
        ];

        $stripeReportPriceId = $stripeReportPriceIds[$reportType];

        if ($user->stripe_customer_id) {
            $user->syncPaymentIntents();
            // $user->cancelDuplicateReports();
        }

        $isReportOrdered = $user->reports->where('type', $reportType)->first();
        $stripeCheckoutSession = null;

        if (!$isReportOrdered) {
            $stripeCheckoutSession = \Stripe\Checkout\Session::create([
                'success_url' => str_replace('CHECKOUT_SESSION_ID', '{CHECKOUT_SESSION_ID}', $input['success_url']),
                'cancel_url' => $input['cancel_url'],
                'customer' => $user->createOrGetStripeCustomer()->id,
                'payment_method_types' => ['card'],
                'mode' => 'payment',
                'line_items' => [
                    ['price' => $stripeReportPriceId, 'quantity' => 1],
                ],
                'payment_intent_data' => [
                    'metadata' => [
                        'type' => 'Report',
                        'id' => $reportType,
                    ],
                ],
            ]);
        }

        return [
            'data' => [
                'checkout_url' => $stripeCheckoutSession->url ?? null,
                'is_ordered' => $isReportOrdered,
                'reports' => $user->reports,
            ],
        ];
    }

    public function validateReport(UserReportValidateRequest $request, User $user, $reportType)
    {
        if (!in_array($reportType, ['natal-chart', 'synastry-report', 'transit-report'])) {
            abort(400, 'Bad Request');
        }

        $input = $request->validated();
        $stripeCheckoutSession = \Stripe\Checkout\Session::retrieve($input['stripe_checkout_session_id']);
        $stripeCustomer = $user->createOrGetStripeCustomer();

        if ($stripeCheckoutSession->customer !== $stripeCustomer->id) {
            throw new BadRequestHttpException('Wrong Stripe Customer');
        }

        $user->syncPaymentIntents();
        $user->load(['reports']);

        return new UserResource($user);
    }

    public function createReport(UserReportCreateRequest $request, User $user, $reportType)
    {
        if (!in_array($reportType, ['natal-chart', 'synastry-report', 'transit-report'])) {
            abort(400, 'Bad Request');
        }

        $input = $request->validated();

        if ($user->stripe_customer_id) {
            $user->syncPaymentIntents();
        }

        $isReportOrdered = $user->reports->where('type', $reportType)->first();
        $stripeCustomer = $user->createOrGetStripeCustomer();
        $user->updateStripeCustomerDefaultPaymentMethod($input['stripe_payment_method_id']);

        if (!$isReportOrdered) {
            $stripeReportPrice = \Stripe\Price::retrieve(
                env('STRIPE_REPORT_' . Str::replace('-', '_', Str::upper($reportType)) . '_PRICE_ID')
            );

            \Stripe\PaymentIntent::create([
                'customer' => $stripeCustomer->id,
                'payment_method' => $input['stripe_payment_method_id'],
                'currency' => $stripeReportPrice->currency,
                'amount' => $stripeReportPrice->unit_amount,
                'confirm' => true,
                'metadata' => [
                    'type' => 'Report',
                    'id' => $reportType,
                ],
            ]);

            $user->syncPaymentIntents();
            $user->load('reports');

            return [
                'data' => [
                    'has_been_ordered' => !!$user->reports->where('type', $reportType)->first(),
                    'reports' => $user->reports,
                ],
            ];
        }

        return [
            'data' => [
                'has_been_ordered' => false,
                'reports' => $user->reports,
            ],
        ];
    }
}
