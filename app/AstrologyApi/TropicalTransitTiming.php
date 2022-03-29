<?php

namespace App\AstrologyApi;

class TropicalTransitTiming
{
    public $transitRelation;

    public function __construct($data)
    {
        $this->transitRelation = array_map(function ($data) {
            return new TropicalTransitRelation($data);
        }, $data['transit_relation']);
    }
}
