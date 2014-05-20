<?php

namespace Wallabag\Api;

use Wallabag\Util\Url;

class ContentFullTextRssApi
{

    public function fetchUrl($url) {

        $url = new Url($url);

        $content = $url->extract();
        return array('title' => $content['title'], 'content' => $content['body']);
    }
}
