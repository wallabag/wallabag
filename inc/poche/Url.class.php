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

    public function isCorrect()
    {
        $pattern = '|^(.*:)//([a-z\-.]+)(:[0-9]+)?(.*)$|i';

        return preg_match($pattern, $this->url);
    }

    public function clean()
    {
        $url = html_entity_decode(trim($this->url));

        $stuff = strpos($url,'&utm_source=');
        if ($stuff !== FALSE)
            $url = substr($url, 0, $stuff);
        $stuff = strpos($url,'?utm_source=');
        if ($stuff !== FALSE)
            $url = substr($url, 0, $stuff);
        $stuff = strpos($url,'#xtor=RSS-');
        if ($stuff !== FALSE)
            $url = substr($url, 0, $stuff);

        $this->url = $url;
    }

    public function fetchContent()
    {
        if ($this->isCorrect()) {
            $this->clean();
            $html = Encoding::toUTF8(Tools::getFile($this->getUrl()));

            # if Tools::getFile() if not able to retrieve HTTPS content, try the same URL with HTTP protocol
            if (!preg_match('!^https?://!i', $this->getUrl()) && (!isset($html) || strlen($html) <= 0)) {
                $this->setUrl('http://' . $this->getUrl());
                $html = Encoding::toUTF8(Tools::getFile($this->getUrl()));
            }

            if (function_exists('tidy_parse_string')) {
                $tidy = tidy_parse_string($html, array(), 'UTF8');
                $tidy->cleanRepair();

				//Warning: tidy might fail so, ensure there is still a content
				$body = $tidy->body();

				//hasChildren does not seem to work, just check the string
				//returned (and do not forget to clean the white spaces)
				if (preg_replace('/\s+/', '', $body->value) !== "<body></body>")
					$html = $tidy->value;
            } 

            $parameters = array();
            if (isset($html) and strlen($html) > 0)
            {
                $readability = new Readability($html, $this->getUrl());
                $readability->convertLinksToFootnotes = CONVERT_LINKS_FOOTNOTES;
                $readability->revertForcedParagraphElements = REVERT_FORCED_PARAGRAPH_ELEMENTS;

                if($readability->init())
                {
                    $content = $readability->articleContent->innerHTML;
                    $parameters['title'] = $readability->articleTitle->innerHTML;
                    $parameters['content'] = $content;

                    return $parameters;
                }
            }
        }
        else {
            #$msg->add('e', _('error during url preparation : the link is not valid'));
            Tools::logm($this->getUrl() . ' is not a valid url');
        }

        return FALSE;
    }
}