<?php

namespace App\Repositories;

use App\City;
use App\Area;
use App\Road;

class AddressRepository
{
    protected $album;

    // 透過 DI 注入 Model
    public function __construct(City $city, Area $area, Road $road)
    {
        $this->city = $city;
        $this->area = $area;
        $this->road = $road;
    }

    // Create city and get id, return 0 if failed.
    public function createCity($data)
    {
        return $this->city->insertGetId($data);
    }

    // Create area and get id, return 0 if failed.
    public function createArea($data)
    {
        return $this->area->insertGetId($data);
    }

    // Insert multiple roads
    public function insertRoads($data)
    {
        return $this->road->insert($data);
    }

    public function getFileNameByArea($data)
    {
        return $this->area->where('name', $data)->pluck('filename')->first();
    }
}
