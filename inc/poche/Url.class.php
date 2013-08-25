<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <support@inthepoche.com>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

class Url
{
    public $url;

    private $fingerprints = array(
        // Posterous
        '<meta name="generator" content="Posterous"' => array('hostname'=>'fingerprint.posterous.com', 'head'=>true),
        // Blogger
        '<meta content=\'blogger\' name=\'generator\'' => array('hostname'=>'fingerprint.blogspot.com', 'head'=>true),
        '<meta name="generator" content="Blogger"' => array('hostname'=>'fingerprint.blogspot.com', 'head'=>true),
        // WordPress (self-hosted and hosted)
        '<meta name="generator" content="WordPress' => array('hostname'=>'fingerprint.wordpress.com', 'head'=>true)
    );

    private $user_agents = array( 'lifehacker.com' => 'PHP/5.2',
                                   'gawker.com' => 'PHP/5.2',
                                   'deadspin.com' => 'PHP/5.2',
                                   'kotaku.com' => 'PHP/5.2',
                                   'jezebel.com' => 'PHP/5.2',
                                   'io9.com' => 'PHP/5.2',
                                   'jalopnik.com' => 'PHP/5.2',
                                   'gizmodo.com' => 'PHP/5.2',
                                   '.wikipedia.org' => 'Mozilla/5.2'
                                  );

    private $content_type_exc = array( 
                                   'application/pdf' => array('action'=>'link', 'name'=>'PDF'),
                                   'image' => array('action'=>'link', 'name'=>'Image'),
                                   'audio' => array('action'=>'link', 'name'=>'Audio'),
                                   'video' => array('action'=>'link', 'name'=>'Video')
                                  );

    private $rewrite_url = array(
        // Rewrite public Google Docs URLs to point to HTML view:
        // if a URL contains docs.google.com, replace /Doc? with /View?
        'docs.google.com' => array('/Doc?' => '/View?'),
        'tnr.com' => array('tnr.com/article/' => 'tnr.com/print/article/'),
        '.m.wikipedia.org' => array('.m.wikipedia.org' => '.wikipedia.org')
    );

    private $rewrite_relative_urls = true;
    private $error_message = '[unable to retrieve full-text content]';

    function __construct($url)
    {
        $this->url = base64_decode($url);
    }

    public function getUrl() {
        return $this->url;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function isCorrect() {
        return filter_var($this->url, FILTER_VALIDATE_URL) !== FALSE;
    }

    public function extract() {
        global $http, $extractor;
        $extractor = new ContentExtractor(dirname(__FILE__).'/../3rdparty/site_config/custom', dirname(__FILE__).'/../3rdparty/site_config/standard');
        $extractor->fingerprints = $this->fingerprints;

        $http = new HumbleHttpAgent();
        $http->userAgentMap = $this->user_agents;
        $http->headerOnlyTypes = array_keys($this->content_type_exc);
        $http->rewriteUrls = $this->rewrite_url;
        $http->userAgentDefault = HumbleHttpAgent::UA_PHP;
        // configure SimplePie HTTP extension class to use our HumbleHttpAgent instance
        SimplePie_HumbleHttpAgent::set_agent($http);
        $feed = new SimplePie();
        // some feeds use the text/html content type - force_feed tells SimplePie to process anyway
        $feed->force_feed(true);
        $feed->set_file_class('SimplePie_HumbleHttpAgent');
        $feed->feed_url = $this->url;
        $feed->set_autodiscovery_level(SIMPLEPIE_LOCATOR_NONE);
        $feed->set_timeout(20);
        $feed->enable_cache(false);
        $feed->set_stupidly_fast(true);
        $feed->enable_order_by_date(false); // we don't want to do anything to the feed
        $feed->set_url_replacements(array());
        // initialise the feed
        // the @ suppresses notices which on some servers causes a 500 internal server error
        $result = @$feed->init();
        if ($result && (!is_array($feed->data) || count($feed->data) == 0)) {
            die('Sorry, no feed items found');
        }
        // from now on, we'll identify ourselves as a browser
        $http->userAgentDefault = HumbleHttpAgent::UA_BROWSER;
        unset($feed, $result);

        $feed = new DummySingleItemFeed($this->url);

        $items = $feed->get_items(0, 1); 
        // Request all feed items in parallel (if supported)
        $urls_sanitized = array();
        $urls = array();
        foreach ($items as $key => $item) {
            $permalink = htmlspecialchars_decode($item->get_permalink());
            // Colons in URL path segments get encoded by SimplePie, yet some sites expect them unencoded
            $permalink = str_replace('%3A', ':', $permalink);
            if ($permalink) {
                $urls_sanitized[] = $permalink;
            }
            $urls[$key] = $permalink;
        }
        $http->fetchAll($urls_sanitized);

        foreach ($items as $key => $item) {
            $do_content_extraction = true;
            $extract_result = false;
            $permalink = $urls[$key];

            // TODO: Allow error codes - some sites return correct content with error status
            // e.g. prospectmagazine.co.uk returns 403

            if ($permalink && ($response = $http->get($permalink, true)) && ($response['status_code'] < 300 || $response['status_code'] > 400)) {
                $effective_url = $response['effective_url'];
                // check if action defined for returned Content-Type
                $type = null;
                if (preg_match('!^Content-Type:\s*(([-\w]+)/([-\w\+]+))!im', $response['headers'], $match)) {
                    // look for full mime type (e.g. image/jpeg) or just type (e.g. image)
                    $match[1] = strtolower(trim($match[1]));
                    $match[2] = strtolower(trim($match[2]));
                    foreach (array($match[1], $match[2]) as $_mime) {
                        if (isset($this->content_type_exc[$_mime])) {
                            $type = $match[1];
                            $_act = $this->content_type_exc[$_mime]['action'];
                            $_name = $this->content_type_exc[$_mime]['name'];
                            if ($_act == 'exclude') {
                                continue 2; // skip this feed item entry
                            } elseif ($_act == 'link') {
                                if ($match[2] == 'image') {
                                    $html = "<a href=\"$effective_url\"><img src=\"$effective_url\" alt=\"$_name\" /></a>";
                                } else {
                                    $html = "<a href=\"$effective_url\">Download $_name</a>";
                                }
                                $title = $_name;
                                $do_content_extraction = false;
                                break;
                            }
                        }
                    }
                    unset($_mime, $_act, $_name, $match);
                }
                if ($do_content_extraction) {
                    $html = $response['body'];
                    // remove strange things
                    $html = str_replace('</[>', '', $html);
                    $html = $this->convert_to_utf8($html, $response['headers']);

                    // check site config for single page URL - fetch it if found
                    if ($single_page_response = $this->getSinglePage($item, $html, $effective_url)) {
                        $html = $single_page_response['body'];
                        // remove strange things
                        $html = str_replace('</[>', '', $html); 
                        $html = $this->convert_to_utf8($html, $single_page_response['headers']);
                        $effective_url = $single_page_response['effective_url'];
                        unset($single_page_response);
                    }
                    $extract_result = $extractor->process($html, $effective_url);
                    $readability = $extractor->readability;
                    $content_block = ($extract_result) ? $extractor->getContent() : null;
                }
            }
            if ($do_content_extraction) {
                // if we failed to extract content...
                if (!$extract_result) {
                    $html = $this->error_message;
                    // keep the original item description
                    $html .= $item->get_description();
                } else {
                    $readability->clean($content_block, 'select');
                    if ($this->rewrite_relative_urls) $this->makeAbsolute($effective_url, $content_block);
                    if ($content_block->childNodes->length == 1 && $content_block->firstChild->nodeType === XML_ELEMENT_NODE) {
                        $html = $content_block->firstChild->innerHTML;
                    } else {
                        $html = $content_block->innerHTML;
                    }
                    // post-processing cleanup
                    $html = preg_replace('!<p>[\s\h\v]*</p>!u', '', $html);
                }
            }
        }

        $title = ($extractor->getTitle() != '' ? $extractor->getTitle() : _('Untitled'));
        $content = array ('title' => $title, 'body' => $html);

        return $content;
    }

    private function convert_to_utf8($html, $header=null)
    {
        $encoding = null;
        if ($html || $header) {
            if (is_array($header)) $header = implode("\n", $header);
            if (!$header || !preg_match_all('/^Content-Type:\s+([^;]+)(?:;\s*charset=["\']?([^;"\'\n]*))?/im', $header, $match, PREG_SET_ORDER)) {
                // error parsing the response
            } else {
                $match = end($match); // get last matched element (in case of redirects)
                if (isset($match[2])) $encoding = trim($match[2], "\"' \r\n\0\x0B\t");
            }
            // TODO: check to see if encoding is supported (can we convert it?)
            // If it's not, result will be empty string.
            // For now we'll check for invalid encoding types returned by some sites, e.g. 'none'
            // Problem URL: http://facta.co.jp/blog/archives/20111026001026.html
            if (!$encoding || $encoding == 'none') {
                // search for encoding in HTML - only look at the first 35000 characters
                $html_head = substr($html, 0, 40000);
                if (preg_match('/^<\?xml\s+version=(?:"[^"]*"|\'[^\']*\')\s+encoding=("[^"]*"|\'[^\']*\')/s', $html_head, $match)) {
                    $encoding = trim($match[1], '"\'');
                } elseif (preg_match('/<meta\s+http-equiv=["\']?Content-Type["\']? content=["\'][^;]+;\s*charset=["\']?([^;"\'>]+)/i', $html_head, $match)) {
                    $encoding = trim($match[1]);
                } elseif (preg_match_all('/<meta\s+([^>]+)>/i', $html_head, $match)) {
                    foreach ($match[1] as $_test) {
                        if (preg_match('/charset=["\']?([^"\']+)/i', $_test, $_m)) {
                            $encoding = trim($_m[1]);
                            break;
                        }
                    }
                }
            }
            if (isset($encoding)) $encoding = trim($encoding);
            // trim is important here!
            if (!$encoding || (strtolower($encoding) == 'iso-8859-1')) {
                // replace MS Word smart qutoes
                $trans = array();
                $trans[chr(130)] = '&sbquo;';    // Single Low-9 Quotation Mark
                $trans[chr(131)] = '&fnof;';    // Latin Small Letter F With Hook
                $trans[chr(132)] = '&bdquo;';    // Double Low-9 Quotation Mark
                $trans[chr(133)] = '&hellip;';    // Horizontal Ellipsis
                $trans[chr(134)] = '&dagger;';    // Dagger
                $trans[chr(135)] = '&Dagger;';    // Double Dagger
                $trans[chr(136)] = '&circ;';    // Modifier Letter Circumflex Accent
                $trans[chr(137)] = '&permil;';    // Per Mille Sign
                $trans[chr(138)] = '&Scaron;';    // Latin Capital Letter S With Caron
                $trans[chr(139)] = '&lsaquo;';    // Single Left-Pointing Angle Quotation Mark
                $trans[chr(140)] = '&OElig;';    // Latin Capital Ligature OE
                $trans[chr(145)] = '&lsquo;';    // Left Single Quotation Mark
                $trans[chr(146)] = '&rsquo;';    // Right Single Quotation Mark
                $trans[chr(147)] = '&ldquo;';    // Left Double Quotation Mark
                $trans[chr(148)] = '&rdquo;';    // Right Double Quotation Mark
                $trans[chr(149)] = '&bull;';    // Bullet
                $trans[chr(150)] = '&ndash;';    // En Dash
                $trans[chr(151)] = '&mdash;';    // Em Dash
                $trans[chr(152)] = '&tilde;';    // Small Tilde
                $trans[chr(153)] = '&trade;';    // Trade Mark Sign
                $trans[chr(154)] = '&scaron;';    // Latin Small Letter S With Caron
                $trans[chr(155)] = '&rsaquo;';    // Single Right-Pointing Angle Quotation Mark
                $trans[chr(156)] = '&oelig;';    // Latin Small Ligature OE
                $trans[chr(159)] = '&Yuml;';    // Latin Capital Letter Y With Diaeresis
                $html = strtr($html, $trans);
            }           
            if (!$encoding) {
                $encoding = 'utf-8';
            } else {
                if (strtolower($encoding) != 'utf-8') {
                    $html = SimplePie_Misc::change_encoding($html, $encoding, 'utf-8');
                    /*
                    if (function_exists('iconv')) {
                        // iconv appears to handle certain character encodings better than mb_convert_encoding
                        $html = iconv($encoding, 'utf-8', $html);
                    } else {
                        $html = mb_convert_encoding($html, 'utf-8', $encoding);
                    }
                    */
                }
            }
        }
        return $html;
    }

    private function makeAbsolute($base, $elem) {
        $base = new SimplePie_IRI($base);
        // remove '//' in URL path (used to prevent URLs from resolving properly)
        // TODO: check if this is still the case
        if (isset($base->path)) $base->path = preg_replace('!//+!', '/', $base->path);
        foreach(array('a'=>'href', 'img'=>'src') as $tag => $attr) {
            $elems = $elem->getElementsByTagName($tag);
            for ($i = $elems->length-1; $i >= 0; $i--) {
                $e = $elems->item($i);
                //$e->parentNode->replaceChild($articleContent->ownerDocument->createTextNode($e->textContent), $e);
                $this->makeAbsoluteAttr($base, $e, $attr);
            }
            if (strtolower($elem->tagName) == $tag) $this->makeAbsoluteAttr($base, $elem, $attr);
        }
    }

    private function makeAbsoluteAttr($base, $e, $attr) {
        if ($e->hasAttribute($attr)) {
            // Trim leading and trailing white space. I don't really like this but 
            // unfortunately it does appear on some sites. e.g.  <img src=" /path/to/image.jpg" />
            $url = trim(str_replace('%20', ' ', $e->getAttribute($attr)));
            $url = str_replace(' ', '%20', $url);
            if (!preg_match('!https?://!i', $url)) {
                if ($absolute = SimplePie_IRI::absolutize($base, $url)) {
                    $e->setAttribute($attr, $absolute);
                }
            }
        }
    }

    private function makeAbsoluteStr($base, $url) {
        $base = new SimplePie_IRI($base);
        // remove '//' in URL path (causes URLs not to resolve properly)
        if (isset($base->path)) $base->path = preg_replace('!//+!', '/', $base->path);
        if (preg_match('!^https?://!i', $url)) {
            // already absolute
            return $url;
        } else {
            if ($absolute = SimplePie_IRI::absolutize($base, $url)) {
                return $absolute;
            }
            return false;
        }
    }

    // returns single page response, or false if not found
    private function getSinglePage($item, $html, $url) {
        global $http, $extractor;
        $host = @parse_url($url, PHP_URL_HOST);
        $site_config = SiteConfig::build($host);
        if ($site_config === false) {
            // check for fingerprints
            if (!empty($extractor->fingerprints) && ($_fphost = $extractor->findHostUsingFingerprints($html))) {
                $site_config = SiteConfig::build($_fphost);
            }
            if ($site_config === false) $site_config = new SiteConfig();
            SiteConfig::add_to_cache($host, $site_config);
            return false;
        } else {
            SiteConfig::add_to_cache($host, $site_config);
        }
        $splink = null;
        if (!empty($site_config->single_page_link)) {
            $splink = $site_config->single_page_link;
        } elseif (!empty($site_config->single_page_link_in_feed)) {
            // single page link xpath is targeted at feed
            $splink = $site_config->single_page_link_in_feed;
            // so let's replace HTML with feed item description
            $html = $item->get_description();
        }
        if (isset($splink)) {
            // Build DOM tree from HTML
            $readability = new Readability($html, $url);
            $xpath = new DOMXPath($readability->dom);
            // Loop through single_page_link xpath expressions
            $single_page_url = null;
            foreach ($splink as $pattern) {
                $elems = @$xpath->evaluate($pattern, $readability->dom);
                if (is_string($elems)) {
                    $single_page_url = trim($elems);
                    break;
                } elseif ($elems instanceof DOMNodeList && $elems->length > 0) {
                    foreach ($elems as $item) {
                        if ($item instanceof DOMElement && $item->hasAttribute('href')) {
                            $single_page_url = $item->getAttribute('href');
                            break;
                        } elseif ($item instanceof DOMAttr && $item->value) {
                            $single_page_url = $item->value;
                            break;
                        }
                    }
                }
            }
            // If we've got URL, resolve against $url
            if (isset($single_page_url) && ($single_page_url = $this->makeAbsoluteStr($url, $single_page_url))) {
                // check it's not what we have already!
                if ($single_page_url != $url) {
                    // it's not, so let's try to fetch it...
                    $_prev_ref = $http->referer;
                    $http->referer = $single_page_url;
                    if (($response = $http->get($single_page_url, true)) && $response['status_code'] < 300) {
                        $http->referer = $_prev_ref;
                        return $response;
                    }
                    $http->referer = $_prev_ref;
                }
            }
        }
        return false;
    }
}