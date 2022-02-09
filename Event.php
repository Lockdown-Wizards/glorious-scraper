<?php

class Event {
    private $event_id;
    private $post_id;
    private $url;
    private $title;
    private $description;
    private $start_date;
    private $end_date;
    private $image;
    private $slug;
    private $featured;

    function __construct($url) {
        if (str_contains($url, '/events/')) {
            $this->event_id = explode('/events/', $url)[1];
        }
        else {
            $this->event_id = 0;
        }
        $this->post_id = 0;
        $this->url = $url;
        $this->title = "";
        $this->description = "";
        $this->start_date = "";
        $this->end_date = "";
        $this->image = "";
        $this->slug = "";
        $this->featured = "";
    }
    
    // All getters and setters

    public function get_event_id() {
        return $this->event_id;
    }
    public function set_event_id($event_id) {
        $this->event_id = $event_id;
    }

    public function get_post_id() {
        return $this->post_id;
    }
    public function set_post_id($post_id) {
        $this->post_id = $post_id;
    }

    public function get_url() {
        return $this->url;
    }
    public function set_url($url) {
        $this->url = $url;
    }

    public function get_title() {
        return $this->title;
    }
    public function set_title($title) {
        $this->title = $title;
    }

    public function get_description() {
        return $this->description;
    }
    public function set_description($description) {
        $this->description = $description;
    }

    public function get_start_date() {
        return $this->start_date;
    }
    public function set_start_date($start_date) {
        $this->start_date = $start_date;
    }

    public function get_end_date() {
        return $this->end_date;
    }
    public function set_end_date($end_date) {
        $this->end_date = $end_date;
    }

    public function get_image() {
        return $this->image;
    }
    public function set_image($image) {
        $this->image = $image;
    }

    public function get_slug() {
        return $this->slug;
    }
    public function set_slug($slug) {
        $this->slug = $slug;
    }

    public function get_featured() {
        return $this->featured;
    }
    public function set_featured($featured) {
        $this->featured = $featured;
    }

    // DOM functions for extracting what we need from facebook pages.

}