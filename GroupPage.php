<?php
// Used in cron-job.php
class GroupPage
{
    private $url; // The group page url
    private $scrape; // Results of the group page scrape
    private $has_scraped; // Has the group page been successfully scraped
    private $scrape_status; // Error codes if something went wrong while scraping the group page.
    private $event_pages; // The event urls within the group page
    private $execution_time; // Amount of seconds it took to perform all the work needed for this group page.

    function __construct($page_url)
    {
        $this->url = $page_url;
        $this->scrape = "";
        $this->has_scraped = false;
        $this->scrape_status = "";
        $this->event_pages = [];
        $this->execution_time = 0;
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

    public function get_execution_time()
    {
        return $this->execution_time;
    }
    public function add_execution_time($seconds)
    {
        $this->execution_time += $seconds;
    }
    public function set_execution_time($seconds)
    {
        $this->execution_time = $seconds;
    }
    public function get_formatted_execution_time() {
        $bit = array(
            'y' => $this->execution_time / 31556926 % 12,
            'w' => $this->execution_time / 604800 % 52,
            'd' => $this->execution_time / 86400 % 7,
            'h' => $this->execution_time / 3600 % 24,
            'm' => $this->execution_time / 60 % 60,
            's' => $this->execution_time % 60
            );
            
        foreach($bit as $k => $v)
            if($v > 0)$ret[] = $v . $k;
            
        return join(' ', $ret);
        // Output example: 6d 15h 48m 19s
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
            "event_pages" => $this->serialize_event_pages(),
            "execution_time" => $this->execution_time,
            "execution_time_readable" => $this->get_formatted_execution_time()
        ];
    }

    public function serialize_to_text() {
        $indent = '   ';
        $bullet_point = '|-- ';

        $log_entry = $bullet_point . "Group Page Url: " . $this->url;
        $log_entry .= "\n" . $bullet_point . "Raw Scraped Data: " . $this->scrape;
        $log_entry .= "\n" . $bullet_point . "Scrape Successful: " . $this->has_scraped;
        $log_entry .= "\n" . $bullet_point . "Scrape Error: " . $this->scrape_status;
        $log_entry .= "\n" . $bullet_point . "Execution Time: " . $this->get_formatted_execution_time();
        foreach ($this->event_pages as $i => $event_page) {
            $log_entry .= "\n" . $bullet_point . "Event Page #" . ($i+1) . ":";
            $log_entry .= "\n" . $event_page->serialize_to_text();
        }
        $log_entry .= "\n-\n";
        return $log_entry;
    }

    // Helper function for serialize_to_log function
    private function minify_html($html) {
        return preg_replace(
            array(
                '/ {2,}/',
                '/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s'
            ),
            array(
                ' ',
                ''
            ),
            $html
        );
    }
}
?>