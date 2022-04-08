<?php
// Used in cron-job.php
class EventPage
{
    private $url;
    private $event;
    private $has_created_event;
    private $event_status;
    private $venue;
    private $has_created_venue;
    private $venue_status;

    function __construct($url)
    {
        $this->url = $url;
        $this->event = "";
        $this->has_created_event = false;
        $this->event_status = "";
        $this->venue = "";
        $this->has_created_venue = false;
        $this->venue_status = "";
    }

    public function get_url()
    {
        return $this->url;
    }

    public function get_event()
    {
        return $this->event;
    }
    public function set_event($event)
    {
        $this->event = $event;
    }

    public function get_has_created_event()
    {
        return $this->has_created_event;
    }
    public function set_has_created_event($has_created_event)
    {
        $this->has_created_event = $has_created_event;
    }

    public function get_event_status()
    {
        return $this->event_status;
    }
    public function set_event_status($event_status)
    {
        $this->event_status = $event_status;
    }

    public function get_venue()
    {
        return $this->venue;
    }
    public function set_venue($venue)
    {
        $this->venue = $venue;
    }

    public function get_has_created_venue()
    {
        return $this->has_created_venue;
    }
    public function set_has_created_venue($has_created_venue)
    {
        $this->has_created_venue = $has_created_venue;
    }

    public function get_venue_status()
    {
        return $this->venue_status;
    }
    public function set_venue_status($venue_status)
    {
        $this->venue_status = $venue_status;
    }

    public function serialize() {
        return [
            "url" => $this->url,
            "event" => $this->event,
            "has_created_event" => $this->has_created_event,
            "event_status" => $this->event_status,
            "venue" => $this->venue,
            "has_created_venue" => $this->has_created_venue,
            "venue_status" => $this->venue_status
        ];
    }
}
?>