<?php
return array(
    'test_url' => 'http://www.npr.org/blogs/thesalt/2013/09/17/223345977/auto-brewery-syndrome-apparently-you-can-make-beer-in-your-gut',
    'body' => array(
         '//div[@id="storytext"]',
    ),
    'strip' => array(
        '//script',
        '//style',
        '//*[@class="bucket img"]',
        '//*[@class="creditwrap"]',
        '//*[@class="captionwrap"]',
        '//*[contains(@class, "enlargebtn")]',
    ),
);
