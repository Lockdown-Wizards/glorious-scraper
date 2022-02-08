<?php

class Event {
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

    public function get_post_id() {
        return $post_id;
    }
    public function set_post_id($post_id) {
        $this->post_id = $post_id;
    }

    public function get_url() {
        return $url;
    }
    public function set_url($url) {
        $this->url = $url;
    }

    public function get_title() {
        return $title;
    }
    public function set_title($title) {
        $this->title = $title;
    }

    public function get_description() {
        return $description;
    }
    public function set_description($description) {
        $this->description = $description;
    }

    public function get_start_date() {
        return $start_date;
    }
    public function set_start_date($start_date) {
        $this->start_date = $start_date;
    }

    public function get_end_date() {
        return $end_date;
    }
    public function set_end_date($end_date) {
        $this->end_date = $end_date;
    }

    public function get_image() {
        return $image;
    }
    public function set_image($image) {
        $this->image = $image;
    }

    public function get_slug() {
        return $slug;
    }
    public function set_slug($slug) {
        $this->slug = $slug;
    }

    public function get_featured() {
        return $featured;
    }
    public function set_featured($featured) {
        $this->featured = $featured;
    }

    // DOM functions for extracting what we need from facebook pages.

}