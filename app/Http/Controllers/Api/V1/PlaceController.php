<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PlaceAutocompleteRequest;
use App\Http\Resources\PlaceResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PlaceController extends Controller
{
    public function autocomplete(PlaceAutocompleteRequest $request)
    {
        $input = $request->validated();

        $response = Http::get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
            'key' => env('GOOGLE_MAPS_API_KEY'),
            'types' => '(cities)',
            'input' => $input['input'],
            'language' => $input['locale'] ?? 'en',
        ]);

        $data = $response->json();

        if ($data['status'] !== 'OK' && $data['status'] !== 'ZERO_RESULTS') {
            return response($data, 500);
        }

        foreach ($data['predictions'] as &$prediction) {
            $prediction['description_html'] = $prediction['description'];
            $globalOffset = 0;

            foreach ($prediction['matched_substrings'] as $matchedSubstring) {
                $matchValue = mb_substr($prediction['description'], $matchedSubstring['offset'], $matchedSubstring['length']);
                $lengthBefore = mb_strlen($prediction['description_html']);

                $prediction['description_html'] = mb_substr_replace(
                    $prediction['description_html'],
                    '<span>' . $matchValue . '</span>',
                    $globalOffset + $matchedSubstring['offset'],
                    $matchedSubstring['length']
                );

                $globalOffset += mb_strlen($prediction['description_html']) - $lengthBefore;
            }
        }

        return PlaceResource::collection($data['predictions']);
    }
}
