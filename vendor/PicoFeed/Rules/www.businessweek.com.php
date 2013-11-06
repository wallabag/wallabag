<?php
return array(
    'test_url' => 'http://www.businessweek.com/articles/2013-09-18/elon-musks-hyperloop-will-work-says-some-very-smart-software',
    'body' => array(
        '//div[@id="lead_graphic"]',
        '//div[@id="article_body"]',
    ),
    'strip' => array(
        '//*[contains(@class, "related_item")]',
    ),
);
