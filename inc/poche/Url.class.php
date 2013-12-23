<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <support@inthepoche.com>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

class Url
{
    public $url;

    function __construct($url)
    {
        $this->url = base64_decode($url);
    }

    public function getUrl() {
        return $this->url;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function isCorrect() {
        return filter_var($this->url, FILTER_VALIDATE_URL) !== FALSE;
    }
}