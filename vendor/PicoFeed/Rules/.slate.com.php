<?php
return array(
    'test_url' => 'http://www.slate.com/articles/business/moneybox/2013/08/microsoft_ceo_steve_ballmer_retires_a_firsthand_account_of_the_company_s.html',
    'body' => array(
        '//div[@class="sl-art-body"]',
    ),
    'strip' => array(
        '//*[contains(@class, "social") or contains(@class, "comments") or contains(@class, "sl-article-floatin-tools")  or contains(@class, "sl-art-pag")]',
        '//*[@id="mys_slate_logged_in"]',
        '//*[@id="sl_article_tools_myslate_bottom"]',
        '//*[@id="mys_myslate"]',
        '//*[@class="sl-viral-container"]',
        '//*[@class="sl-art-creds-cntr"]',
        '//*[@class="sl-art-ad-midflex"]',
    )
);