<?php
return array(
    'test_url' => 'http://www.wired.com/gamelife/2013/09/ouya-free-the-games/',
    'body' => array(
         '//div[@class="entry"]',
    ),
    'strip' => array(
        '//script',
        '//style',
        '//*[@id="linker_widget"]',
        '//*[contains(@class, "bio")]',
        '//*[contains(@class, "entry-footer")]',
        '//*[contains(@class, "mobify_backtotop_link")]',
        '//*[contains(@class, "gallery-navigation")]',
        '//*[contains(@class, "gallery-thumbnail")]',
        '//img[contains(@src, "1x1")]',
        '//a[contains(@href, "creativecommons")]',
    ),
);
