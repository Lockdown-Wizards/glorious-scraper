<?php

class Venue
{
    private $title;
    private $country;
    private $city;
    private $state;
    private $province;
    private $zip;
    private $phone;

    function __construct()
    {
        $this->title = "";
        $this->country = "United States";
        $this->city = "";
        $this->state = "";
        $this->title = "";
        $this->province = "";
        $this->zip = "";
        $this->phone = "";
    }

    // All getters and setters

    public function get_title()
    {
        return $this->title;
    }
    public function set_title($title)
    {
        $this->title = $title;
    }

    public function get_country()
    {
        return $this->country;
    }
    public function set_country($country)
    {
        $this->country = $country;
    }

    public function get_address()
    {
        return $this->address;
    }
    public function set_address($address)
    {
        $this->address = $address;
    }

    public function get_city()
    {
        return $this->city;
    }
    public function set_city($city)
    {
        $this->city = $city;
    }

    public function get_state()
    {
        return $this->state;
    }
    public function set_state($state)
    {
        return $this->state = $state;
    }

    public function get_province()
    {
        return $this->province;
    }
    public function set_province($province)
    {
        $this->province = $province;
    }

    public function get_zip()
    {
        return $this->zip;
    }
    public function set_zip($zip)
    {
        $this->zip = $zip;
    }
    
    public function get_phone()
    {
        return $this->phone;
    }
    public function set_phone($phone)
    {
        $this->phone = $phone;
    }

    public function to_args()
    {
        return [
            'Venue' => $this->title,
            'Country' => $this->country,
            'City' => $this->city,
            'State' => $this->state,
            'Address' => $this->title,
            'Province' => $this->province,
            'Zip' => $this->zip,
            'Phone' => $this->phone,
        ];
    }
}
