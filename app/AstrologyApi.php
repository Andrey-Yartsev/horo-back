<?php

namespace App;

use Illuminate\Support\Facades\Http;

class AstrologyApi
{
    const BASE_URL = 'https://json.astrologyapi.com/v1/';
    public static $transaction = null;

    public static function start()
    {
        self::$transaction = [
            'master' => curl_multi_init(),
            'handles' => [],
        ];
    }

    public static function end()
    {
        if (!self::$transaction) {
            return;
        }

        $running = null;

        do {
            curl_multi_exec(self::$transaction['master'], $running);
        } while ($running);

        foreach (self::$transaction['handles'] as $handle) {
            curl_multi_remove_handle(self::$transaction['master'], $handle);
        }

        curl_multi_close(self::$transaction['master']);

        $results = array_map(function ($handle) {
            return json_decode(curl_multi_getcontent($handle), true);
        }, self::$transaction['handles']);

        return $results;
    }

    public static function client()
    {
        return Http::withBasicAuth(config('astrology-api.user_id'), config('astrology-api.api_key'));
    }

    public static function post($path, $data = [])
    {
        $ch = curl_init(self::BASE_URL . $path);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_USERPWD, config('astrology-api.user_id') . ':' . config('astrology-api.api_key'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10000);

        if (self::$transaction) {
            self::$transaction['handles'][] = $ch;
            curl_multi_add_handle(self::$transaction['master'], $ch);
            return;
        }

        $result = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $result;
    }

    public static function westernHoroscope($data)
    {
        return self::post('western_horoscope', $data);
    }

    public static function lifeForecastReportTropical($data)
    {
        return self::post('life_forecast_report/tropical', $data);
    }

    public static function tropicalTransitsDaily($data)
    {
        return self::post('tropical_transits/daily', $data);
    }

    public static function generalSignReportTropical($planetName, $data)
    {
        return self::post('general_sign_report/tropical/' . $planetName, $data);
    }

    public static function natalTransitsDaily($data)
    {
        return self::post('natal_transits/daily', $data);
    }

    public static function houseCuspsTropical($data)
    {
        return self::post('house_cusps/tropical', $data);
    }

    public static function zodiacCompatibility($sign0Code, $sign1Code, $data = [])
    {
        return self::post('zodiac_compatibility/' . $sign0Code . '/' . $sign1Code, $data);
    }
}
