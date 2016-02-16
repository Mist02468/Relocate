<?php

namespace Application\Model;

class CompanyLocation
{
    public $id;
    public $city_id;
    public $company_id;
    public $coordinatesNorth;
    public $coordinatesWest;

    public function exchangeArray($data)
    {
        $this->id                 = (!empty($data['id']))   ? $data['id'] : null;
        $this->city_id            = (!empty($data['city_id'])) ? $data['city_id'] : null;
        $this->company_id         = (!empty($data['company_id'])) ? $data['company_id'] : null;
        $this->coordinatesNorth   = (!empty($data['coordinatesNorth'])) ? $data['coordinatesNorth'] : null;
        $this->coordinatesWest    = (!empty($data['coordinatesWest'])) ? $data['coordinatesWest'] : null;
    }
}
