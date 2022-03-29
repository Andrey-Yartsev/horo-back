<?php

namespace App\AstrologyApi;

use App\AstrologyApi\Planet;
use App\AstrologyApi\AstrologicalHouse;

class WesternHoroscope
{
    public $houses;
    public $planets;

    public function __construct($data)
    {
        $this->houses = array_map(function ($data) {
            return new AstrologicalHouse($data);
        }, $data['houses']);

        $this->planets = array_map(function ($data) {
            return new Planet($data);
        }, $data['planets']);
    }

    public function housesDegrees()
    {
        return array_map(function ($house) {
            return $house->degree;
        }, $this->houses);
    }

    public function degreesBetweenHousesAngles()
    {
        $resultAngles = [];

        foreach ($this->houses as $index => $house) {
            $newHouse = $house;

            if (isset($this->houses[$index + 1])) {
                $newHouse = $this->houses[$index + 1];
            } else {
                $newHouse = $this->houses[0];
            }

            $dividedAngle = $newHouse->degree - $house->degree;

            if ($dividedAngle < 0) {
                $dividedAngle = 360 - abs($dividedAngle);
            }

            $middleAngle = $dividedAngle / 2;
            $resultAngle = $middleAngle + $house->degree;
            $resultAngles[$house->house] = $resultAngle;
        }

        return $resultAngles;
    }

    public function firstHouseAngleOffset()
    {
        if (isset($this->houses[0])) {
            return $this->houses[0]->degree - 180;
        }

        return null;
    }
}
