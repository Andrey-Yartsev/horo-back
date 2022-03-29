<?php

namespace App;

use Google\Cloud\Translate\V2\TranslateClient;

class GoogleTranslate
{
    public static function translate($texts = [], $localeTo, $localeFrom = 'en')
    {
        if (count($texts) === 0) {
            return $texts;
        }

        if ($localeTo === $localeFrom) {
            return $texts;
        }

        $textKeys = array_keys($texts);
        $translate = new TranslateClient(['key' => env('GOOGLE_MAPS_API_KEY')]);

        $results = $translate->translateBatch(array_values($texts), [
            'source' => $localeFrom,
            'target' => $localeTo,
        ]);

        return collect($results)->mapWithKeys(function ($result, $index) use ($textKeys) {
            return [$textKeys[$index] => $result['text']];
        })->toArray();
    }
}
