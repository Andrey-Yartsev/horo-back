<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    // ---------------------------------------------------------------------- //

    // protected $attributes = [
    //     //
    // ];

    // protected $fillable = [
    //     //
    // ];

    // protected $hidden = [
    //     //
    // ];

    // protected $casts = [
    //     //
    // ];

    // protected $appends = [
    //     //
    // ];

    // Attributes
    // ---------------------------------------------------------------------- //

    // put here getSomeAttribute & setSomeAttribute methods

    // Scopes
    // ---------------------------------------------------------------------- //

    // put here different scopes here to use them to build query

    // Relations
    // ---------------------------------------------------------------------- //

    // put here relation methods

    // Non-static methods
    // ---------------------------------------------------------------------- //

    public function check()
    {
        $stripeSubscription = \Stripe\Subscription::retrieve($this->stripe_id);

        if ($this->status !== $stripeSubscription->status) {
            $this->status = $stripeSubscription->status;
        }

        $this->last_checked_at = now();
        $this->save();
    }

    // Static methods
    // ---------------------------------------------------------------------- //

    // public static function create(array $data)
    // {
    //     $thing = new self;
    //     $thing->fill($data);
    //     $thing->save();

    //     return $thing;
    // }
}
