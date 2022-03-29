<?php

namespace App\Http\Controllers\Api\V1;

use App\AstrologyApi;
use App\Models\Planet;
use App\GoogleTranslate;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PlanetResource;

class PlanetController extends Controller
{
    public function get(Request $request, $planetSlug)
    {
        $planet = Planet::where('name', $planetSlug)->firstOrFail();
        $basicDataForAstrologyApi = auth()->user()->getBasicDataForAstrologyApi();
        $data = AstrologyApi::generalSignReportTropical($planet->name, $basicDataForAstrologyApi);
        $planet->sign = $data['sign_name'];
        $toTranslate['description'] = $planet->description;
        $toTranslate['report'] = $data['report'];
        $toTranslate['planetText'] = "Your $planet->name: in";
        $toTranslate['planetInSignText'] = "in $planet->sign.";
        $translated = GoogleTranslate::translate($toTranslate, app()->getLocale());
        $planet->description = $translated['description'];
        $planet->report = $translated['report'];

        $planet->inSignText = implode(' ', [
            Str::before($translated['planetText'], ':'),
            Str::before($translated['planetInSignText'], '.'),
        ]);

        return new PlanetResource($planet);
    }
}
