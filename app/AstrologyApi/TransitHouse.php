<?php

namespace App\AstrologyApi;

class TransitHouse
{
    public $planet;
    public $transitHouse;
    public $transitHouseWithOffset;

    public function __construct($data)
    {
        $this->planet = $data['planet'];
        $this->transitHouse = $data['transit_house'];
        $this->transitHouseWithOffset = null;
    }
}
