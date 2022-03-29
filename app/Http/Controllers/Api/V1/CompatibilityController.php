<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\AstrologyApi;
use App\GoogleTranslate;
use App\AstrologyApi\Sign;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\CompatibilityResource;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class CompatibilityController extends Controller
{
    public function index(Request $request, $sign0Code, $sign1Code)
    {
        $data = AstrologyApi::zodiacCompatibility($sign0Code, $sign1Code);

        if (isset($data['error'])) {
            throw new Exception($data['error']);
        }

        [$sign0Code, $sign1Code] = collect([$sign0Code, $sign1Code])->sort()->values()->toArray();

        if (!$compatibilityValues = Cache::get('compatibility_' . $sign0Code . '_' . $sign1Code)) {
            $compatibilityValues = [];
            $initialValue = $data['compatibility_percentage'];

            for ($compatibilityValueIndex = 0; $compatibilityValueIndex < 6; ++$compatibilityValueIndex) {
                $randomMinimumPercentage = rand(1, 20);
                $randomMaximumPercentage = rand(1, 15);
                $minimumPercentage = $initialValue * $randomMinimumPercentage / 100;
                $maximumPercentage = $initialValue * $randomMaximumPercentage / 100;
                $minimumResult = $initialValue - $minimumPercentage;
                $maximumResult = $initialValue - $maximumPercentage;
                $valueResults = collect([$minimumResult, $maximumResult]);
                $randomElement = $valueResults->random();

                if ($randomElement < 50) {
                    $compatibilityValues[$compatibilityValueIndex] = 50 + rand(1, 15);
                    continue;
                }

                if ($randomElement > 97) {
                    $compatibilityValues[$compatibilityValueIndex] = 97 - rand(1, 20);
                    continue;
                }

                $compatibilityValues[$compatibilityValueIndex] = $randomElement;
            }

            Cache::forever('compatibility_' . $sign0Code . '_' . $sign1Code, $compatibilityValues);
        }

        $conclusion = (
            Sign::COMPATIBILITY_MAP[$data['your_sign']][$data['your_partner_sign']] ??
            Sign::COMPATIBILITY_MAP[$data['your_partner_sign']][$data['your_sign']] ??
            null
        );

        $toTranslate = [];
        $toTranslate['report'] = $data['compatibility_report'];
        $toTranslate['conclusion'] = $conclusion;
        $translated = GoogleTranslate::translate($toTranslate, app()->getLocale());

        return new CompatibilityResource([
            'sign0' => $data['your_sign'],
            'sign1' => $data['your_partner_sign'],
            'conclusion' => $translated['conclusion'],
            'report' => $translated['report'],
            'percentage' => $data['compatibility_percentage'],
            'values' => $compatibilityValues,
        ]);
    }
}
