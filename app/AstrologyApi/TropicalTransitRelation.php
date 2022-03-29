<?php

namespace App\AstrologyApi;

use Carbon\Carbon;

class TropicalTransitRelation
{
    public $transitPlanet;
    public $natalPlanet;
    public $startTime;
    public $endTime;

    public function __construct($data)
    {
        $this->transitPlanet = $data['transit_planet'];
        $this->natalPlanet = $data['natal_planet'];
        $this->startTime = $data['start_time'];
        $this->endTime = $data['end_time'];
    }

    public function transitionEndTimeString()
    {
        return (new Carbon($this->endTime))->isoFormat('DD MMMM');
    }

    public function transitionStartTimeString()
    {
        return (new Carbon($this->startTime))->isoFormat('DD MMMM');
    }

    public function wasTransitionStarted()
    {
        return (new Carbon($this->startTime))->getTimestamp() < time();
    }

    public function getTransitionRelationTimeString()
    {
        return (new Carbon($this->startTime))->isoFormat('DD MMM') . ' - ' . (new Carbon($this->endTime))->isoFormat('DD MMM');
    }

    public function getTransitionProgressFloat()
    {
        $duration = (new Carbon($this->endTime))->getTimestamp() - (new Carbon($this->startTime))->getTimestamp();
        $elapsed = time() - (new Carbon($this->startTime))->getTimestamp();
        $percentage = $elapsed / $duration;

        return $percentage < 0 ? 0 : $percentage;
    }
}
