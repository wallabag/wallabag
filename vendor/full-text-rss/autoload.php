<?php

require_once __DIR__ . '/libraries/readability/Readability.php';

require dirname(__FILE__).'/libraries/simplepie/autoloader.php';

require_once __DIR__ . '/libraries/content-extractor/ContentExtractor.php';
require_once __DIR__ . '/libraries/content-extractor/SiteConfig.php';
require_once __DIR__ . '/libraries/humble-http-agent/HumbleHttpAgent.php';
require_once __DIR__ . '/libraries/humble-http-agent/SimplePie_HumbleHttpAgent.php';
require_once __DIR__ . '/libraries/humble-http-agent/CookieJar.php';


////////////////////////////////
// Load config file
////////////////////////////////
require dirname(__FILE__).'/config.php';
