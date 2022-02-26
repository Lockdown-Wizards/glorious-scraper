<?php

class Event
{
    private $event_id;
    private $post_id;
    private $url;
    private $ticket_url;
    private $organization_url;
    private $title;
    private $description;
    private $start_date;
    private $end_date;
    private $start_time;
    private $end_time;
    private $location;
    private $image;
    private $slug;
    private $organization;
    private $featured;
    private $category;

    function __construct($url)
    {
        if (str_contains($url, '/events/')) {
            $this->event_id = explode('/events/', $url)[1];
        } else {
            $this->event_id = 0;
        }
        $this->post_id = 0;
        $this->url = $url;
        $this->ticket_url = "";
        $this->organization_url = "";
        $this->title = "";
        $this->description = "";
        $this->start_date = "";
        $this->end_date = "";
        $this->start_time = "";
        $this->end_time = "";
        $this->location = "";
        $this->image = "";
        $this->slug = "";
        $this->organization = "";
        $this->featured = "";
        $this->categories = [];
        $this->venue = "";
    }

    // All getters and setters

    public function get_event_id()
    {
        return $this->event_id;
    }
    public function set_event_id($event_id)
    {
        $this->event_id = $event_id;
    }

    public function get_post_id()
    {
        return $this->post_id;
    }
    public function set_post_id($post_id)
    {
        $this->post_id = $post_id;
    }

    public function get_url()
    {
        return $this->url;
    }
    public function set_url($url)
    {
        $this->url = $url;
    }

    public function get_ticket_url()
    {
        return $this->ticket_url;
    }
    public function set_ticket_url($ticket_url)
    {
        $this->ticket_url = $ticket_url;
    }

    public function get_organization_url()
    {
        return $this->organization_url;
    }
    public function set_organization_url($organization_url)
    {
        $this->organization_url = $organization_url;
    }

    public function get_title()
    {
        return $this->title;
    }
    public function set_title($title)
    {
        $this->title = $title;
    }

    public function get_description()
    {
        return $this->description;
    }
    public function set_description($description)
    {
        $this->description = $description;
    }

    public function get_start_date()
    {
        return $this->start_date;
    }
    public function set_start_date($start_date)
    {
        $this->start_date = $start_date;
    }

    public function get_end_date()
    {
        return $this->end_date;
    }
    public function set_end_date($end_date)
    {
        $this->end_date = $end_date;
    }

    public function get_start_time()
    {
        return $this->start_time;
    }
    public function set_start_time($start_time)
    {
        $this->start_time = $start_time;
    }

    public function get_end_time()
    {
        return $this->end_time;
    }
    public function set_end_time($end_time)
    {
        $this->end_time = $end_time;
    }

    public function get_location()
    {
        return $this->location;
    }
    public function set_location($location)
    {
        $this->location = $location;
    }

    public function get_image()
    {
        return $this->image;
    }
    public function set_image($image)
    {
        $this->image = $image;
    }

    public function get_slug()
    {
        return $this->slug;
    }
    public function set_slug($slug)
    {
        $this->slug = $slug;
    }

    public function get_organization()
    {
        return $this->organization;
    }
    public function set_organization($organization)
    {
        $this->organization = $organization;
    }

    public function get_featured()
    {
        return $this->featured;
    }
    public function set_featured($featured)
    {
        $this->featured = $featured;
    }

    public function get_categories()
    {
        return $this->categories;
    }
    public function set_categories($categories)
    {
        $this->categories = $categories;
    }

    private function get_meridian($time_str)
    {
        return str_contains($time_str, "PM") ? "PM" : "AM";
    }
    private function get_hour($time_str)
    {
        $hour_value = intval(explode(":", $time_str)[0]);
        return $hour_value < 10 ? ("0" . strval($hour_value)) : strval($hour_value);
    }
    private function get_minutes($time_str)
    {
        return substr(explode(":", $time_str)[1], 0, 2);
    }

    public function is_online()
    {
        return str_contains($this->location, "http");
    }
    public function has_tickets()
    {
        return $this->ticket_url !== "";
    }

    private function formatted_description()
    {
        $header = "<i>Event by: <a style='color: blue !important' href='https://www.facebook.com" . $this->organization_url . "' title='" . $this->organization . "' target='_blank' rel='noopener'>" . $this->organization . "</a></i>";
        $fbEvent = "<b>To view this event on Facebook, please <a style=\"color: blue !important\" href=\"http://www.facebook.com" . $this->url . "\" title=\"View on Facebook\" target=\"_blank\" rel=\"noopener\">click here.</a></b>";
        $directions = "";
        if (!$this->is_online()) {
            $directions = "<b>For directions to this event, please <a style=\"color: blue !important\" href=\"https://maps.google.com/?q=" . urlencode($this->location) . "\" title=\"Get directions\" target=\"_blank\" rel=\"noopener\">click here.</a></b>";
        } else {
            $directions = "<b>This event is online, <a style=\"color: blue !important\" href=\"" . $this->location . "\" title=\"Get directions\" target=\"_blank\" rel=\"noopener\">click here</a> for the link.</b>";
        }
        $tickets = "";
        if ($this->has_tickets()) {
            $tickets = "<b>To purchase tickets for " . $this->title . ", please <a style=\"color: blue !important\" href=\"" . $this->ticket_url . "\" title=\"Buy Tickets\" target=\"_blank\" rel=\"noopener\">click here.</a></b>";
        }
        return $header . "\n" . $this->description . "\n" . $tickets . "\n" . $directions . "\n" . $fbEvent;
    }

    // DOM functions for extracting what we need from facebook pages.
    // https://docs.theeventscalendar.com/reference/functions/tribe_create_event/
    public function to_args()
    {
        $facebook_base_url = 'https://www.facebook.com';
        return [
            'id' => $this->event_id,
            'post_title' => $this->title,
            'EventURL' => $facebook_base_url . $this->url,
            'Location' => $this->location,
            'post_content' => $this->formatted_description(),
            'post_type' => 'tribe_events',
            'EventStartDate' => $this->start_date,
            'EventEndDate' => $this->end_date,
            'EventStartHour' => $this->get_hour($this->start_time),
            'EventStartMinute' => $this->get_minutes($this->start_time),
            'EventStartMeridian' => $this->get_meridian($this->start_time),
            'EventEndHour' => $this->get_hour($this->end_time),
            'EventEndMinute' => $this->get_minutes($this->end_time),
            'EventEndMeridian' => $this->get_meridian($this->end_time),
            'FeaturedImage' => $this->image,
            'Organizer' => $this->organization,
            'post_categories' => $this->categories,
            'comment_status' => 'open',
        ];
    }
}
