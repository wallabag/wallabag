<?php
return array(
    'test_url' => 'http://online.wsj.com/article/SB10001424127887324108204579023143974408428.html',
    'body' => array(
        '//div[@class="articlePage"]',
    ),
    'strip' => array(
        '//*[@id="articleThumbnail_2"]',
        '//*[@class="socialByline"]',
    )
);