<?php
return array(
    'test_url' => 'http://www.slate.fr/monde/77034/allemagne-2013-couacs-campagne',
    'body' => array(
        '//div[@class="article_content"]',
    ),
    'strip' => array(
        '//script',
        '//style',
        '//*[@id="slate_associated_bn"]',
        '//*[@id="ligatus-article"]',
        '//*[@id="article_sidebar"]',
        '//div[contains(@id, "reseaux")]',
        '//*[contains(@class, "smart") or contains(@class, "article_tags") or contains(@class, "article_reactions")]',
        '//*[contains(@class, "OUTBRAIN") or contains(@class, "related_item") or contains(@class, "share")]',
    )
);