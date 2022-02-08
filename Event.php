<?php

class Event {
    public $url;

    function __construct($url, $urlId) {
        $this->urlId = $urlId;
        $this->url = $url;
    }
}