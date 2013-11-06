<?php
return array(
    'test_url' => 'http://www.theguardian.com/law/2013/aug/31/microsoft-google-sue-us-fisa',
    'body' => array(
        '//div[@id="article-wrapper"]',
    ),
    'strip' => array(
        '//*[contains(@class, "promo")]',
    ),
);
