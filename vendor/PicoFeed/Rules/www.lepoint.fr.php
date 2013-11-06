<?php
return array(
    'test_url' => 'http://www.lepoint.fr/c-est-arrive-aujourd-hui/19-septembre-1783-pour-la-premiere-fois-un-mouton-un-canard-et-un-coq-s-envoient-en-l-air-devant-louis-xvi-18-09-2012-1507704_494.php',
    'body' => array(
        '//article',
    ),
    'strip' => array(
        '//script',
        '//style',
        '//*[contains(@class, "info_article")]',
        '//*[contains(@class, "fildariane_titre")]',
        '//*[contains(@class, "entete2_article")]',
        '//*[contains(@class, "signature_article")]',
        '//*[contains(@id, "share")]',
    )
);