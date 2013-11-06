<?php
return array(
    'test_url' => 'http://www.cnn.com/2013/08/31/world/meast/syria-civil-war/index.html?hpt=hp_t1',
    'body' => array(
        '//div[@class="cnn_strycntntlft"]',
    ),
    'strip' => array(
        '//script',
        '//style',
        '//div[@class="cnn_stryshrwdgtbtm"]',
        '//div[@class="cnn_strybtmcntnt"]',
        '//div[@class="cnn_strylftcntnt"]',
        '//div[contains(@class, "cnnGalleryContainer")]',
        '//div[contains(@class, "cnn_strylftcexpbx")]',
        '//div[contains(@class, "articleGalleryNavContainer")]',
        '//div[contains(@class, "cnnArticleGalleryCaptionControl")]',
        '//div[contains(@class, "cnnArticleGalleryNavPrevNextDisabled")]',
        '//div[contains(@class, "cnnArticleGalleryNavPrevNext")]',
        '//div[contains(@class, "cnn_html_media_title_new")]',
        '//div[contains(@id, "disqus")]',
    )
);
