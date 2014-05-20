<?php
namespace Wallabag\Util;

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
