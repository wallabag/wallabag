<?php
namespace Wallabag\Util;

class DummySingleItem {
    public $url;
    function __construct($url) { $this->url = $url; }
    public function get_permalink() { return $this->url; }
    public function get_title() { return null; }
    public function get_date($format='') { return false; }
    public function get_author($key=0) { return null; }
    public function get_authors() { return null; }
    public function get_description() { return ''; }
    public function get_enclosure($key=0, $prefer=null) { return null; }
    public function get_enclosures() { return null; }
    public function get_categories() { return null; }
}
