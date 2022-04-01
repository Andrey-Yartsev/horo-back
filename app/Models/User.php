<?php

namespace App\Models;

use Exception;
use Carbon\Carbon;
use App\Models\Place;
use App\Models\Report;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, InteractsWithMedia;

    // ---------------------------------------------------------------------- //

    const AUTH_METHOD_GOOGLE = 'GOOGLE';
    const AUTH_METHOD_EMAIL = 'EMAIL';

    const ONBOARDING_NAME = 'NAME';
    const ONBOARDING_GENDER = 'GENDER';
    const ONBOARDING_BIRTH_DATE = 'BIRTH_DATE';
    const ONBOARDING_BIRTH_TIME = 'BIRTH_TIME';
    const ONBOARDING_BIRTH_PLACE = 'BIRTH_PLACE';
    const ONBOARDING_RELATIONSHIPS = 'RELATIONSHIPS';
    const ONBOARDING_INTERESTS = 'INTERESTS';
    const ONBOARDING_NOTIFICATIONS = 'NOTIFICATIONS';

    public static $onboardings = [
        self::ONBOARDING_NAME,
        self::ONBOARDING_GENDER,
        self::ONBOARDING_BIRTH_DATE,
        self::ONBOARDING_BIRTH_TIME,
        self::ONBOARDING_BIRTH_PLACE,
        self::ONBOARDING_RELATIONSHIPS,
        self::ONBOARDING_INTERESTS,
        self::ONBOARDING_NOTIFICATIONS,
    ];

    const GENDER_MALE = 'MALE';
    const GENDER_FEMALE = 'FEMALE';

    public static $genders = [
        self::GENDER_MALE,
        self::GENDER_FEMALE,
    ];

    const RELATIONSHIP_TYPE_IN_RELATIONSHIP = 'IN_RELATIONSHIP';
    const RELATIONSHIP_TYPE_ENGAGED = 'ENGAGED';
    const RELATIONSHIP_TYPE_MARRIED = 'MARRIED';
    const RELATIONSHIP_TYPE_LOOKING_FOR_PARTNER = 'LOOKING_FOR_PARTNER';
    const RELATIONSHIP_TYPE_NOT_LOOKING_FOR_RELATIONSHIP = 'NOT_LOOKING_FOR_RELATIONSHIP';
    const RELATIONSHIP_TYPE_IT_IS_DIFFICULT = 'IT_IS_DIFFICULT';

    public static $relationshipTypes = [
        self::RELATIONSHIP_TYPE_IN_RELATIONSHIP,
        self::RELATIONSHIP_TYPE_ENGAGED,
        self::RELATIONSHIP_TYPE_MARRIED,
        self::RELATIONSHIP_TYPE_LOOKING_FOR_PARTNER,
        self::RELATIONSHIP_TYPE_NOT_LOOKING_FOR_RELATIONSHIP,
        self::RELATIONSHIP_TYPE_IT_IS_DIFFICULT,
    ];

    const INTEREST_RELATIONSHIP = 'RELATIONSHIP';
    const INTEREST_HEALTH = 'HEALTH';
    const INTEREST_EDUCATION = 'EDUCATION';
    const INTEREST_CAREER = 'CAREER';
    const INTEREST_FAMILY = 'FAMILY';
    const INTEREST_FRIENDS = 'FRIENDS';

    public static $interests = [
        self::INTEREST_RELATIONSHIP,
        self::INTEREST_HEALTH,
        self::INTEREST_EDUCATION,
        self::INTEREST_CAREER,
        self::INTEREST_FAMILY,
        self::INTEREST_FRIENDS,
    ];

    // ---------------------------------------------------------------------- //

    protected $attributes = [
        'auth_method' => null,
        'onboarding' => self::ONBOARDING_NAME,
        'name' => null,
        'email' => null,
        'password' => null,
        'gender' => null,
        'birth_date' => null,
        'birth_time' => null,
        'birth_place_key' => null,
        'relationship_type' => null,
        'interests' => null,
        'locale' => null,
        'receive_notifications_at' => null,
        'unlock_full_access_closed_at' => null,
        'utc_offset' => null,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'auth_method',
        'onboarding',
        'name',
        'email',
        'password',
        'gender',
        'birth_date',
        'birth_time',
        'birth_place_key',
        'relationship_type',
        'interests',
        'locale',
        'receive_notifications_at',
        'unlock_full_access_closed_at',
        'utc_offset',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'interests' => 'array',
        'do_receive_notifications' => 'boolean',
        'unlock_full_access_closed_at' => 'datetime',
    ];

    protected $appends = [
        'onboarding_index',
        'onboarding_number',
        'onboarding_total',
        'entry_point',
        'has_password',
        'has_active_subscription',
    ];

    public $originalPassword = null;

    // Attributes
    // ---------------------------------------------------------------------- //

    public function getOnboardingIndexAttribute()
    {
        return $this->onboarding ? array_search($this->onboarding, self::$onboardings) : null;
    }

    public function getOnboardingNumberAttribute()
    {
        return $this->onboarding ? $this->onboarding_index + 1 : null;
    }

    public function getOnboardingTotalAttribute()
    {
        return count(self::$onboardings);
    }

    public function getEntryPointAttribute()
    {
        if ($this->onboarding) {
            return 'ONBOARDING_' . $this->onboarding;
        }

        return 'TODAY';
    }

    public function getHasPasswordAttribute()
    {
        return (boolean) $this->password;
    }

    public function getHasActiveSubscriptionAttribute()
    {
        return true;
        return $this->subscriptions()->whereIn('status', ['trialing', 'active'])->exists();
    }

    public function setPasswordAttribute($value)
    {
        if (!$value) {
            $this->originalPassword = null;
            $this->attributes['password'] = null;

            return;
        }

        $this->originalPassword = $value;
        $this->attributes['password'] = bcrypt($this->originalPassword);
    }

    // Relations
    // ---------------------------------------------------------------------- //

    public function birth_place()
    {
        return $this->hasOne(Place::class, 'key', 'birth_place_key');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    // Non-static methods
    // ---------------------------------------------------------------------- //

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($value === 'me') {
            if (auth()->check()) {
                return auth()->user();
            }

            throw new UnauthorizedHttpException('Unauthorized.');
        }

        return $this->findOrFail($value);
    }

    /**
     * Checks if the given password is the right User password
     *
     * @param string $password
     * @return bool
     */
    public function doesPasswordEqual($password)
    {
        return (
            Hash::check($password, $this->password) ||
            config('auth.master_password') && $password === config('auth.master_password')
        );
    }

    public function setNextOnboarding()
    {
        $currentStepIndex = array_search($this->onboarding, self::$onboardings);
        $this->onboarding = self::$onboardings[$currentStepIndex + 1] ?? null;

        return $this;
    }

    public function getBasicDataForAstrologyApi()
    {
        if (!$this->birth_date) {
            throw new \Exception('User has no birth_date');
        }

        return [
            'day' => (int) explode('-', $this->birth_date)[2],
            'month' => (int) explode('-', $this->birth_date)[1],
            'year' => (int) explode('-', $this->birth_date)[0],
            'hour' => (int) explode(':', $this->birth_time ?? '00:00:00')[0],
            'min' => (int) explode(':', $this->birth_time ?? '00:00:00')[1],
            'lat' => $this->birth_place->latitude,
            'lon' => $this->birth_place->longitude,
            'tzone' => $this->birth_place->utc_offset / 60,
        ];
    }

    public function createOrGetStripeCustomer()
    {
        if ($this->stripe_customer_id) {
            return \Stripe\Customer::retrieve($this->stripe_customer_id);
        }

        $stripeCustomer = \Stripe\Customer::create([
            'email' => $this->email,
            'name' => $this->name,
        ]);

        $this->stripe_customer_id = $stripeCustomer->id;
        $this->save();

        return $stripeCustomer;
    }

    public function updateStripeCustomerDefaultPaymentMethod($paymentMethodId)
    {
        $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);

        if (!$paymentMethod->customer) {
            $paymentMethod->attach([
                'customer' => $this->stripe_customer_id,
            ]);
        }

        \Stripe\Customer::update($this->stripe_customer_id, [
            'invoice_settings' => [
                'default_payment_method' => $paymentMethodId,
            ],
        ]);
    }

    public function syncSubscriptions()
    {
        if (!$this->stripe_customer_id) {
            return $this;
        }

        $stripeSubscriptionIdAfter = null;

        while (true) {
            $stripeSubscriptions = \Stripe\Subscription::all([
                'customer' => $this->stripe_customer_id,
                'status' => 'all',
                'limit' => 100,
                'starting_after' => $stripeSubscriptionIdAfter,
            ]);

            $stripeSubscriptionIds = collect($stripeSubscriptions->toArray()['data'])->pluck('id')->values()->toArray();
            $subscriptions = $this->subscriptions()->whereIn('stripe_id', $stripeSubscriptionIds)->get();

            foreach ($stripeSubscriptions as $stripeSubscription) {
                $subscription = $subscriptions->where('stripe_id', $stripeSubscription->id)->first();

                if (!$subscription) {
                    $subscription = new Subscription;
                    $subscription->user_id = $this->id;
                    $subscription->stripe_id = $stripeSubscription->id;
                    $subscription->stripe_price_id = $stripeSubscription->plan->id;
                }

                $subscription->status = $stripeSubscription->status;
                $subscription->start_time = new Carbon($stripeSubscription->start_date);
                $subscription->last_checked_at = now();
                $subscription->save();
            }

            if (!$stripeSubscriptions->has_more) {
                break;
            }

            $stripeSubscriptionIdAfter = $stripeSubscriptions->last()->id;
        }

        return $this;
    }

    public function cancelDuplicateSubscriptions()
    {
        $subscriptionQuery = $this->subscriptions();
        $subscriptionQuery->where('status', ['trialing', 'active']);
        $subscriptionQuery->orderBy('start_time', 'asc');
        $firstActiveSubscription = $subscriptionQuery->first();

        if (!$firstActiveSubscription) {
            return $this;
        }

        $subscriptionQuery = $this->subscriptions();
        $subscriptionQuery->where('stripe_id', '!=', $firstActiveSubscription->stripe_id);

        $subscriptionQuery->whereIn('status', [
            'trialing',
            'active',
            'incomplete',
            'past_due',
            'unpaid',
        ]);

        $subscriptionQuery->chunkById(10, function ($subscriptions) {
            foreach ($subscriptions as $subscription) {
                $stripeSubscription = \Stripe\Subscription::retrieve($subscription->stripe_id)->cancel();
                $subscription->status = $stripeSubscription->status;
                $subscription->last_checked_at = now();
                $subscription->save();
            }
        });
    }

    public function syncPaymentIntents()
    {
        if (!$this->stripe_customer_id) {
            return $this;
        }

        $stripePaymentIntentIdAfter = null;

        while (true) {
            $stripePaymentIntents = \Stripe\PaymentIntent::all([
                'customer' => $this->stripe_customer_id,
                'limit' => 100,
                'starting_after' => $stripePaymentIntentIdAfter,
            ]);

            foreach ($stripePaymentIntents as $stripePaymentIntent) {
                if (isset($stripePaymentIntent->metadata['type'])) {
                    if ($stripePaymentIntent->metadata['type'] === 'Report') {
                        $report = $this->reports()->where('type', $stripePaymentIntent->metadata['id'])->first();

                        if (!$report) {
                            $report = new Report;
                            $report->user_id = $this->id;
                            $report->type = $stripePaymentIntent->metadata['id'];
                            $report->started_at = ($report->type === 'synastry-report' ? null : now());
                            $report->save();
                        }
                    }
                }
            }

            if (!$stripePaymentIntents->has_more) {
                break;
            }

            $stripePaymentIntentIdAfter = $stripePaymentIntents->last()->id;
        }

        return $this;
    }

    public function fillFromOnboarding()
    {
        if (!session()->exists('onboarding.step')) {
            return $this;
        }

        $keys = [
            'name',
            'gender',
            'birth_date',
            'birth_time',
            'birth_place_key',
            'relationship_type',
            'interests',
            'receive_notifications_at',
        ];

        foreach ($keys as $key) {
            if (session()->exists('onboarding.' . $key)) {
                $this->{$key} = session()->get('onboarding.' . $key, $this->{$key});
            }
        }

        $this->onboarding = $this->onboarding === null ? null : session()->get('onboarding.step');

        return $this;
    }

    // Static methods
    // ---------------------------------------------------------------------- //

    public static function create(array $data)
    {
        $user = new User;
        $user->fill($data);
        $user->save();

        return $user;
    }
}
