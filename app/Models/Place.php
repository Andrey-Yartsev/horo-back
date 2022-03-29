<?php

namespace App\Models;

use Exception;
use App\Models\PlaceTranslation;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Place extends Model
{
    use HasFactory;

    // ---------------------------------------------------------------------- //

    // protected $attributes = [
    //     //
    // ];

    protected $fillable = [
        'key',
        'description',
        'latitude',
        'longitude',
        'utc_offset',
    ];

    protected $hidden = [
        'relevant_translation',
        'translations',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    protected $appends = [
        'description',
    ];

    // Attributes
    // ---------------------------------------------------------------------- //

    public function getDescriptionAttribute()
    {
        return get_localized_attribute($this, 'description');
    }

    // Scopes
    // ---------------------------------------------------------------------- //

    // put here different scopes here to use them to build query

    // Relations
    // ---------------------------------------------------------------------- //

    public function translations()
    {
        return $this->hasMany(PlaceTranslation::class);
    }

    public function relevant_translation()
    {
        return $this->hasOne(PlaceTranslation::class)->where('locale', app()->getLocale());
    }

    // Non-static methods
    // ---------------------------------------------------------------------- //

    // put here non-static methods

    // Static methods
    // ---------------------------------------------------------------------- //

    public static function getOrMakeByKey($placeKey, $locale = null)
    {
        if (!$placeKey || !is_string($placeKey)) {
            return null;
        }

        $locale = $locale ?? app()->getLocale();
        $place = self::where('key', $placeKey)->first();

        if ($place) {
            if ($locale === 'en') {
                return $place;
            }

            $place->setRelation('relevant_translation', $place->translations()->where('locale', $locale)->first());

            if ($place->relevant_translation) {
                return $place;
            }
        }

        if (!$place) {
            $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
                'key' => env('GOOGLE_MAPS_API_KEY'),
                'placeid' => $placeKey, // 'ChIJmc2sfCutXkERZYyttbl3y38',
                'fields' => 'formatted_address,geometry,utc_offset',
                'language' => 'en',
            ]);

            $data = $response->json();

            if ($data['status'] !== 'OK') {
                throw new Exception($data);
            }

            $place = self::create([
                'key' => $placeKey,
                'description' => $data['result']['formatted_address'],
                'latitude' => $data['result']['geometry']['location']['lat'],
                'longitude' => $data['result']['geometry']['location']['lng'],
                'utc_offset' => $data['result']['utc_offset'],
            ]);

            $place->setRelation('relevant_translation', null);
        }

        if ($locale !== 'en') {
            $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
                'key' => env('GOOGLE_MAPS_API_KEY'),
                'placeid' => $placeKey, // 'ChIJmc2sfCutXkERZYyttbl3y38',
                'fields' => 'formatted_address',
                'language' => $locale,
            ]);

            $data = $response->json();

            if ($data['status'] !== 'OK') {
                throw new Exception($data);
            }

            $place->setRelation('relevant_translation', PlaceTranslation::create([
                'place' => $place,
                'locale' => $locale,
                'description' => $data['result']['formatted_address'],
            ]));
        }

        return $place;
    }

    public static function create(array $data)
    {
        $place = new self;
        $place->fill($data);
        $place->save();

        return $place;
    }
}
