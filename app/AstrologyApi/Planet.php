<?php

namespace App\AstrologyApi;

class Planet
{
    const DISTANCE = [
        'Earth' => 0,
        'Moon' => 1,
        'Venus' => 2,
        'Mars' => 3,
        'Mercury' => 4,
        'Sun' => 5,
        'Jupiter' => 6,
        'Saturn' => 7,
        'Uranus' => 8,
        'Neptune' => 9,
        'Pluto' => 10,
    ];

    public $name;
    public $fullDegree;
    public $house;
    public $sign;

    public function __construct($data)
    {
        $this->name = $data['name'];
        $this->fullDegree = $data['full_degree'];
        $this->house = $data['house'];
        $this->sign = $data['sign'];
    }
}
