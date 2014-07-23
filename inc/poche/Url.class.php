<?php
/**
 * wallabag, self hostable application allowing you to not miss any content anymore
 *
 * @category   wallabag
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://opensource.org/licenses/MIT see COPYING file
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