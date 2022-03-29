<?php

namespace App\AstrologyApi;

class TropicalReport
{
    public $planetPosition;
    public $translatedPlanetPosition;
    public $forecast;
    public $translatedForecast;
    public $date;

    public function __construct($data)
    {
        $this->planetPosition = $data['planet_position'];
        $this->translatedPlanetPosition = null;
        $this->forecast = $data['forecast'];
        $this->translatedForecast = null;
        $this->date = $data['date'];
    }

    public function getDirectionDegrees() {
        $degrees = 0.0;

        foreach (TransitionDegrees::CASES as $transitionName => $transitionDegrees) {
            if (strpos($this->planetPosition, $transitionName) !== false) {
                $degrees = $transitionDegrees;
            }
        }

        return $degrees;
    }

    public function getTransitionPositionDescription() {
        $position = '';

        foreach (TransitionDegrees::CASES as $transitionName => $transitionDegrees) {
            if (strpos($this->planetPosition, $transitionName) !== false) {
                $position = $transitionName;
            }
        }

        return $position;
    }

    public function getFirstPlanetName()
    {
        $parts = preg_split('/\s+/', $this->planetPosition);

        return $parts[1];
    }

    public function getSecondPlanetName()
    {
        $parts = preg_split('/\s+/', $this->planetPosition);

        return $parts[count($parts) - 1];
    }
}
