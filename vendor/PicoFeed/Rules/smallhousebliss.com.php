<?php
return array(
    'test_url' => 'http://smallhousebliss.com/2013/08/29/house-g-by-lode-architecture/',
    'body' => array(
        '//div[@class="single-entry-content"]',
    ),
    'strip' => array(
        '//style',
        '//script',
        '//*[contains(@class, "gallery")]',
        '//*[contains(@class, "share")]',
        '//*[contains(@class, "wpcnt")]',
        '//*[contains(@class, "entry-meta")]',
    )
);
