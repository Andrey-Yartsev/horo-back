<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaceTranslation extends Model
{
    use HasFactory;

    // ---------------------------------------------------------------------- //

    // protected $attributes = [
    //     //
    // ];

    protected $fillable = [
        'locale',
        'description',
    ];

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

    // put here non-static methods

    // Static methods
    // ---------------------------------------------------------------------- //

    public static function create(array $data)
    {
        $placeTranslation = new self;
        $placeTranslation->place_id = $data['place']->id;
        $placeTranslation->fill($data);
        $placeTranslation->save();

        return $placeTranslation;
    }
}
