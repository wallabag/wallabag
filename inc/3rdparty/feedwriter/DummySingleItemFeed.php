<?php
// create single item dummy feed object
class DummySingleItemFeed {
    public $item;
    function __construct($url) { $this->item = new DummySingleItem($url); }
    public function get_title() { return ''; }
    public function get_description() { return 'Content extracted from '.$this->item->url; }
    public function get_link() { return $this->item->url; }
    public function get_language() { return false; }
    public function get_image_url() { return false; }
    public function get_items($start=0, $max=1) { return array(0=>$this->item); }
}
class DummySingleItem {
    public $url;
    function __construct($url) { $this->url = $url; }
    public function get_permalink() { return $this->url; }
    public function get_title() { return ''; }
    public function get_date($format='') { return false; }
    public function get_author($key=0) { return null; }
    public function get_authors() { return null; }
    public function get_description() { return ''; }
    public function get_enclosure($key=0, $prefer=null) { return null; }
    public function get_enclosures() { return null; }
}