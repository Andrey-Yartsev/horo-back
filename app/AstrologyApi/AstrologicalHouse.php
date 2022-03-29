<?php

namespace App\AstrologyApi;

class AstrologicalHouse
{
    public $house;
    public $degree;
    public $sign;

    public function __construct($data)
    {
        $this->house = $data['house'];
        $this->degree = $data['degree'];
        $this->sign = $data['sign'];
    }
}
