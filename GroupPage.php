<?php
// Used in cron-job.php
class GroupPage
{
    private $url; // The group page url
    private $scrape; // Results of the group page scrape
    private $has_scraped; // Has the group page been successfully scraped
    private $scrape_status; // Error codes if something went wrong while scraping the group page.
    private $event_pages; // The event urls within the group page

    function __construct($page_url)
    {
        $this->url = $page_url;
        $this->scrape = "";
        $this->has_scraped = false;
        $this->scrape_status = "";
        $this->event_pages = [];
    }

    public function get_url()
    {
        return $this->url;
    }

    public function get_scrape()
    {
        return $this->scrape;
    }
    public function set_scrape($scrape)
    {
        $this->scrape = $scrape;
    }

    public function get_has_scraped()
    {
        return $this->has_scraped;
    }
    public function set_has_scraped($has_scraped)
    {
        $this->has_scraped = $has_scraped;
    }

    public function get_scrape_status()
    {
        return $this->scrape_status;
    }
    public function set_scrape_status($scrape_status)
    {
        $this->scrape_status = $scrape_status;
    }

    public function add_event_page($event_page) {
        $this->event_pages[] = $event_page;
    }
    public function get_event_page($index) {
        return $this->event_pages[$index];
    }
    public function get_total_event_pages() {
        return count($this->event_pages);
    }

    public function serialize_event_pages() {
        $result = [];
        foreach ($this->event_pages as $event_page) {
            $result[] = $event_page->serialize();
        }
        return $result;
    }

    public function serialize() {
        return [
            "url" => $this->url,
            "scrape" => $this->scrape,
            "has_scraped" => $this->has_scraped,
            "scrape_status" => $this->scrape_status,
            "event_pages" => $this->serialize_event_pages()
        ];
    }
}
?>