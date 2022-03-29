<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use NumberFormatter;
use App\AstrologyApi;
use App\Models\Planet;
use App\GoogleTranslate;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\AstrologyApi\TransitHouse;
use App\AstrologyApi\TropicalReport;
use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\AstrologyApi\WesternHoroscope;
use App\AstrologyApi\TropicalTransitTiming;
use App\AstrologyApi\Planet as AstrologyApiPlanet;

class PageController extends Controller
{
    public function today(Request $request)
    {
        $basicDataForAstrologyApi = auth()->user()->getBasicDataForAstrologyApi();

        AstrologyApi::start();
        AstrologyApi::westernHoroscope($basicDataForAstrologyApi);
        AstrologyApi::lifeForecastReportTropical($basicDataForAstrologyApi);
        AstrologyApi::tropicalTransitsDaily($basicDataForAstrologyApi);
        AstrologyApi::natalTransitsDaily($basicDataForAstrologyApi);
        $data = AstrologyApi::end();

        $horoscope = new WesternHoroscope($data[0]);

        $planets = array_map(function ($data) {
            return new AstrologyApiPlanet($data);
        }, $data[0]['planets']);

        $forecast = array_map(function ($data) {
            return new TropicalReport($data);
        }, $data[1]['life_forecast']);

        $transitHouses = array_map(function ($data) {
            return new TransitHouse($data);
        }, $data[2]['transit_house']);

        $tropicalTransitTiming = new TropicalTransitTiming($data[3]);
        $firstHouseAngleOffset = $horoscope->firstHouseAngleOffset();
        $degreesBetweenHousesAngles = $horoscope->degreesBetweenHousesAngles();
        $planetaryTransits = [];

        foreach ($forecast as $tropicalReportIndex => $tropicalReport) {
            $firstPlanetName = $tropicalReport->getFirstPlanetName();
            $secondPlanetName = $tropicalReport->getSecondPlanetName();
            $firstPlanetDistance = AstrologyApiPlanet::DISTANCE[$firstPlanetName];
            $secondPlanetDistance = AstrologyApiPlanet::DISTANCE[$secondPlanetName];
            $isFirstPlanetCloseToEarh = $firstPlanetDistance < $secondPlanetDistance;
            $firstPlanetAngle = collect($planets)->where('name', $firstPlanetName)->first()->fullDegree;
            $secondPlanetAngle = collect($planets)->where('name', $secondPlanetName)->first()->fullDegree;
            $transitHouse = collect($transitHouses)->where('planet', $firstPlanetName)->first();
            $transitHouseDegrees = collect($horoscope->houses)->where('house', $transitHouse->transitHouse)->first();
            $directionDegrees = $tropicalReport->getDirectionDegrees();
            $planetPosition = $tropicalReport->getTransitionPositionDescription();
            $transitionDate = new Carbon($tropicalReport->date);
            $transitionDateString = $transitionDate->isoFormat('DD MMMM');
            $degreesToTransform = $tropicalReport->getDirectionDegrees();
            $positivePlanetDegrees = $secondPlanetAngle + $degreesToTransform;
            $negativePlanetDegrees = $secondPlanetAngle - $degreesToTransform;
            $negativePlanetDegrees = $negativePlanetDegrees < 0 ? 360 - abs($negativePlanetDegrees) : $negativePlanetDegrees;
            $planetsAnglesToChoose = [$positivePlanetDegrees, $negativePlanetDegrees];

            $planetAngleResult = collect($planetsAnglesToChoose)
                ->sort(function ($a, $b) use ($transitHouseDegrees) {
                    return abs($a - $transitHouseDegrees->degree) - abs($b - $transitHouseDegrees->degree);
                })
                ->first();

            $distanceBetweenAngles = [
                'planetsAngles' => [$firstPlanetAngle, $planetAngleResult < $firstPlanetAngle ? 360 + $planetAngleResult : $planetAngleResult],
                'bigRadius' => !$isFirstPlanetCloseToEarh,
            ];

            $fillSectorsClockwise = $planetAngleResult === $positivePlanetDegrees;

            $fillSectors = [
                'planetsAngles' => [$planetAngleResult, $secondPlanetAngle],
                'clockwise' => $fillSectorsClockwise,
            ];

            $drawPlanets = [];

            $drawPlanets[] = [
                'angle' => $planetAngleResult,
                'bigRadius' => !$isFirstPlanetCloseToEarh,
                'isBig' => true,
                'name' => $transitHouse->planet,
            ];

            $resultsWithOffset = $planetAngleResult;

            $closest = collect($degreesBetweenHousesAngles)
                ->sort(function ($a, $b) use ($resultsWithOffset) {
                    return abs($a - $resultsWithOffset) - abs($b - $resultsWithOffset);
                })
                ->first();

            $closestHouse = $closest->key ?? null;
            $transitHouses[$tropicalReportIndex]->transitHouseWithOffset = $closestHouse;

            if ($firstPlanetName === $secondPlanetName) {
                $drawPlanets[] = [
                    'angle' => $firstPlanetAngle,
                    'bigRadius' => !$isFirstPlanetCloseToEarh,
                    'isBig' => false,
                    'name' => $firstPlanetName,
                ];
            } else {
                $drawPlanets[] = [
                    'angle' => $firstPlanetAngle,
                    'bigRadius' => !$isFirstPlanetCloseToEarh,
                    'isBig' => false,
                    'name' => $firstPlanetName,
                ];

                $drawPlanets[] = [
                    'angle' => $secondPlanetAngle,
                    'bigRadius' => $isFirstPlanetCloseToEarh,
                    'isBig' => false,
                    'name' => $secondPlanetName,
                ];
            }

            $progress = 0;
            $startLabel = null;
            $endLabel = null;
            $isStarted = false;
            $durationString = null;

            if ($transitionDate->getTimestamp() < now()->getTimestamp()) {
                $planetTransit = collect($tropicalTransitTiming->transitRelation)
                    ->where('transitPlanet', $firstPlanetName)
                    ->where('natalPlanet', $secondPlanetName)
                    ->first();

                if ($planetTransit) {
                    $progress = $planetTransit->getTransitionProgressFloat();
                    $startLabel = $transitionDateString;
                    $endLabel = $planetTransit->transitionEndTimeString();
                    $durationString = $planetTransit->getTransitionRelationTimeString();

                    if ($progress >= 0) {
                        $isStarted = true;
                    }
                }
            }

            $transitHouseOrdinal = (new NumberFormatter('en_US', NumberFormatter::ORDINAL))->format($transitHouse->transitHouse);

            $planetaryTransits[] = [
                'id' => preg_replace('/\s+/', '-', strtolower($tropicalReport->planetPosition)),
                'title' => $tropicalReport->planetPosition,
                'description' => $tropicalReport->forecast,
                'firstPlanetName' => $firstPlanetName,
                'secondPlanetName' => $secondPlanetName,
                'firstPlanetAngle' => $firstPlanetAngle,
                'secondPlanetAngle' => $secondPlanetAngle,
                'transitHouse' => $transitHouse,
                'transitHouseTextIn' => "in the $transitHouseOrdinal house",
                'transitHouseDegrees' => $transitHouseDegrees,
                'directionDegrees' => $directionDegrees,
                'transitionDate' => $transitionDate->isoFormat('YYYY-MM-DD'),
                'transitionDateString' => $transitionDateString,
                'progress' => $progress,
                'startLabel' => $startLabel,
                'endLabel' => $endLabel,
                'isStarted' => $isStarted,
                'drawPlanets' => $drawPlanets,
                'distanceBetweenAngles' => $distanceBetweenAngles,
                'fillSectors' => $fillSectors,
                'rotationOffset' => $firstHouseAngleOffset,
                'anglesOfAstrologicalHouses' => $horoscope->housesDegrees(),
                'degreesBetweenHousesAngles' => $degreesBetweenHousesAngles,
                'durationString' => $durationString,
                'planetPosition' => $planetPosition,
            ];
        }

        $toTranslate = [];

        foreach ($planetaryTransits as $planetaryTransitIndex => &$planetaryTransit) {
            $toTranslate[$planetaryTransitIndex . '_' . 'title'] = $planetaryTransit['title'];
            $toTranslate[$planetaryTransitIndex . '_' . 'description'] = $planetaryTransit['description'];
            $toTranslate[$planetaryTransitIndex . '_' . 'transitHouseTextIn'] = $planetaryTransit['transitHouseTextIn'];
        }

        $translated = GoogleTranslate::translate($toTranslate, app()->getLocale());

        foreach ($planetaryTransits as $planetaryTransitIndex => &$planetaryTransit) {
            $planetaryTransit['title'] = $translated[$planetaryTransitIndex . '_' . 'title'];
            $planetaryTransit['description'] = $translated[$planetaryTransitIndex . '_' . 'description'];
            $planetaryTransit['transitHouseTextIn'] = $translated[$planetaryTransitIndex . '_' . 'transitHouseTextIn'];
        }

        $todayString = now()->addMinutes(auth()->user()->utc_offset)->isoFormat('ddd, MMM D, YYYY');

        return new PageResource([
            'planetaryTransits' => $planetaryTransits,
            'todayString' => $todayString,
        ]);
    }

    public function youBasic(Request $request)
    {
        return new PageResource([
            'sunPlanet' => ['sign' => 'aquarius'],
            'sunInSignText' => 'xzc',
        ]);

        $sunPlanet = Planet::where('name', 'Sun')->first();
        print_r($sunPlanet) ;die('xxx');
        $basicDataForAstrologyApi = auth()->user()->getBasicDataForAstrologyApi();
        $data = AstrologyApi::generalSignReportTropical($sunPlanet->name, $basicDataForAstrologyApi);
        $sunPlanet->sign = $data['sign_name'];
        $toTranslate['sunInSignText'] = "$sunPlanet->name in $sunPlanet->sign";
        $translated = GoogleTranslate::translate($toTranslate, app()->getLocale());


        return new PageResource([
            'sunPlanet' => $sunPlanet->toArray(),
            'sunInSignText' => $translated['sunInSignText'],
        ]);
    }

    public function youPlanets(Request $request)
    {
        $planets = Planet::get();
        $basicDataForAstrologyApi = auth()->user()->getBasicDataForAstrologyApi();
        AstrologyApi::start();

        foreach ($planets as $planet) {
            AstrologyApi::generalSignReportTropical($planet->name, $basicDataForAstrologyApi);
        }

        $data = AstrologyApi::end();

        foreach ($planets as $planetIndex => $planet) {
            $planet->sign = $data[$planetIndex]['sign_name'];
        }

        $toTranslate = [];

        foreach ($planets as $planetIndex => &$planet) {
            $planet->sign = $data[$planetIndex]['sign_name'];
            $toTranslate[$planetIndex . '_' . 'your_planet'] = "Your {$planet->name}: in";
            $toTranslate[$planetIndex . '_' . 'in_sign_text'] = "in {$planet->sign}.";
        }

        $translated = GoogleTranslate::translate($toTranslate, app()->getLocale());

        foreach ($planets as $planetIndex => &$planet) {
            $planet->your_planet_in_sign_text = implode(' ', [
                Str::before($translated[$planetIndex . '_' . 'your_planet'], ':'),
                Str::before($translated[$planetIndex . '_' . 'in_sign_text'], '.'),
            ]);
        }

        return new PageResource([
            'planets' => $planets->toArray(),
        ]);
    }

    public function youHouses(Request $request)
    {
        $data = AstrologyApi::houseCuspsTropical(auth()->user()->getBasicDataForAstrologyApi());

        return new PageResource([
            'houses' => $data,
        ]);
    }
}
