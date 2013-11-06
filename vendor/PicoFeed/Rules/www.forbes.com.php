<?php
return array(
    'test_url' => 'http://www.forbes.com/sites/andygreenberg/2013/09/05/follow-the-bitcoins-how-we-got-busted-buying-drugs-on-silk-roads-black-market/',
    'body' => array(
        '//div[@id="leftRail"]/div[contains(@class, body)]',
    ),
    'strip' => array(
        '//aside',
        '//div[contains(@class, "entity_block")]',
        '//div[contains(@class, "vestpocket") and not contains(@class, "body")]',
        '//div[contains(@style, "display")]',
        '//div[contains(@id, "comment")]',
        '//div[contains(@class, "widget")]',
        '//div[contains(@class, "pagination")]',
    )
);