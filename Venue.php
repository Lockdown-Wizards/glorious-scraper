<?php

class Venue
{
    private $venue_id;
    private $title;
    private $country;
    private $city;
    private $state;
    private $province;
    private $zip;
    private $phone;

    function __construct($url)
    {
        $this->venue_id = 0;
        $this->title = "";
        $this->country = "";
        $this->city = "";
        $this->state = "";
        $this->title = "";
        $this->province = "";
        $this->zip = "";
        $this->phone = "";
    }

    // All getters and setters

    public function get_venue_id()
    {
        return $this->venue_id;
    }
    public function set_venue_id($venue_id)
    {
        $this->venue_id = $venue_id;
    }

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
            'id' => $this->venue_id,
            'title' => $this->title,
            'country' => $this->country,
            'city' => $this->city,
            'state' => $this->state,
            'address' => $this->title,
            'province' => $this->province,
            'zip' => $this->zip,
            'phone' => $this->phone,
        ];
    }
}
