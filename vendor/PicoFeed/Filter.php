<?php

namespace PicoFeed;

class Filter
{
    private $data = '';
    private $url = '';
    private $input = '';
    private $empty_tags = array();
    private $strip_content = false;
    private $is_code = false;

    // Allow only these tags and attributes
    public static $whitelist_tags = array(
        'audio' => array('controls', 'src'),
        'video' => array('poster', 'controls', 'height', 'width', 'src'),
        'source' => array('src', 'type'),
        'dt' => array(),
        'dd' => array(),
        'dl' => array(),
        'table' => array(),
        'caption' => array(),
        'tr' => array(),
        'th' => array(),
        'td' => array(),
        'tbody' => array(),
        'thead' => array(),
        'h2' => array(),
        'h3' => array(),
        'h4' => array(),
        'h5' => array(),
        'h6' => array(),
        'strong' => array(),
        'em' => array(),
        'code' => array(),
        'pre' => array(),
        'blockquote' => array(),
        'p' => array(),
        'ul' => array(),
        'li' => array(),
        'ol' => array(),
        'br' => array(),
        'del' => array(),
        'a' => array('href'),
        'img' => array('src'),
        'figure' => array(),
        'figcaption' => array(),
        'cite' => array(),
        'time' => array('datetime'),
        'abbr' => array('title'),
        'iframe' => array('width', 'height', 'frameborder', 'src'),
        'q' => array('cite')
    );

    // Strip content of these tags
    public static $blacklist_tags = array(
        'script'
    );

    // Allowed URI scheme
    // For a complete list go to http://en.wikipedia.org/wiki/URI_scheme
    public static $scheme_whitelist = array(
        '//',
        'data:image/png;base64,',
        'data:image/gif;base64,',
        'data:image/jpg;base64,',
        'bitcoin:',
        'callto:',
        'ed2k://',
        'facetime://',
        'feed:',
        'ftp://',
        'geo:',
        'git://',
        'http://',
        'https://',
        'irc://',
        'irc6://',
        'ircs://',
        'jabber:',
        'magnet:',
        'mailto:',
        'nntp://',
        'rtmp://',
        'sftp://',
        'sip:',
        'sips:',
        'skype:',
        'smb://',
        'sms:',
        'spotify:',
        'ssh:',
        'steam:',
        'svn://',
        'tel:',
    );

    // Attributes used for external resources
    public static $media_attributes = array(
        'src',
        'href',
        'poster',
    );

    // Blacklisted resources
    public static $media_blacklist = array(
        'feeds.feedburner.com',
        'share.feedsportal.com',
        'da.feedsportal.com',
        'rss.feedsportal.com',
        'res.feedsportal.com',
        'res1.feedsportal.com',
        'res2.feedsportal.com',
        'res3.feedsportal.com',
        'pi.feedsportal.com',
        'rss.nytimes.com',
        'feeds.wordpress.com',
        'stats.wordpress.com',
        'rss.cnn.com',
        'twitter.com/home?status=',
        'twitter.com/share',
        'twitter_icon_large.png',
        'www.facebook.com/sharer.php',
        'facebook_icon_large.png',
        'plus.google.com/share',
        'www.gstatic.com/images/icons/gplus-16.png',
        'www.gstatic.com/images/icons/gplus-32.png',
        'www.gstatic.com/images/icons/gplus-64.png',
    );

    // Mandatory attributes for specified tags
    public static $required_attributes = array(
        'a' => array('href'),
        'img' => array('src'),
        'iframe' => array('src'),
        'audio' => array('src'),
        'source' => array('src'),
    );

    // Add attributes to specified tags
    public static $add_attributes = array(
        'a' => 'rel="noreferrer" target="_blank"'
    );

    // Attributes that must be integer
    public static $integer_attributes = array(
        'width',
        'height',
        'frameborder',
    );

    // Iframe source whitelist, everything else is ignored
    public static $iframe_whitelist = array(
        'http://www.youtube.com/',
        'https://www.youtube.com/',
        'http://player.vimeo.com/',
        'https://player.vimeo.com/',
        'http://www.dailymotion.com',
        'https://www.dailymotion.com',
    );


    // All inputs data must be encoded in UTF-8 before
    public function __construct($data, $site_url)
    {
        $this->url = $site_url;

        \libxml_use_internal_errors(true);

        // Convert bad formatted documents to XML
        $dom = new \DOMDocument;
        $dom->loadHTML('<?xml version="1.0" encoding="UTF-8">'.$data);
        $this->input = $dom->saveXML($dom->getElementsByTagName('body')->item(0));
    }


    public function execute()
    {
        $parser = xml_parser_create();
        xml_set_object($parser, $this);
        xml_set_element_handler($parser, 'startTag', 'endTag');
        xml_set_character_data_handler($parser, 'dataTag');
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
        xml_parse($parser, $this->input, true); // We ignore parsing error (for old libxml)
        xml_parser_free($parser);

        $this->data = $this->removeEmptyTags($this->data);
        $this->data = $this->removeMultipleTags($this->data);

        return $this->data;
    }


    public function startTag($parser, $name, $attributes)
    {
        $empty_tag = false;
        $this->strip_content = false;

        if ($this->is_code === false && $name === 'pre') $this->is_code = true;

        if ($this->isPixelTracker($name, $attributes)) {

            $empty_tag = true;
        }
        else if ($this->isAllowedTag($name)) {

            $attr_data = '';
            $used_attributes = array();

            foreach ($attributes as $attribute => $value) {

                if ($value != '' && $this->isAllowedAttribute($name, $attribute)) {

                    if ($this->isResource($attribute)) {

                        if ($name === 'iframe') {

                            if ($this->isAllowedIframeResource($value)) {

                                $attr_data .= ' '.$attribute.'="'.$value.'"';
                                $used_attributes[] = $attribute;
                            }
                        }
                        else if ($this->isRelativePath($value)) {

                            $attr_data .= ' '.$attribute.'="'.$this->getAbsoluteUrl($value, $this->url).'"';
                            $used_attributes[] = $attribute;
                        }
                        else if ($this->isAllowedProtocol($value) && ! $this->isBlacklistedMedia($value)) {

                            if ($attribute == 'src' &&
                                isset($attributes['data-src']) &&
                                $this->isAllowedProtocol($attributes['data-src']) &&
                                ! $this->isBlacklistedMedia($attributes['data-src'])) {

                                $value = $attributes['data-src'];
                            }

                            // Replace protocol-relative url // by http://
                            if (substr($value, 0, 2) === '//') $value = 'http:'.$value;

                            $attr_data .= ' '.$attribute.'="'.$value.'"';
                            $used_attributes[] = $attribute;
                        }
                    }
                    else if ($this->validateAttributeValue($attribute, $value)) {

                        $attr_data .= ' '.$attribute.'="'.$value.'"';
                        $used_attributes[] = $attribute;
                    }
                }
            }

            // Check for required attributes
            if (isset(self::$required_attributes[$name])) {

                foreach (self::$required_attributes[$name] as $required_attribute) {

                    if (! in_array($required_attribute, $used_attributes)) {

                        $empty_tag = true;
                        break;
                    }
                }
            }

            if (! $empty_tag) {

                $this->data .= '<'.$name.$attr_data;

                // Add custom attributes
                if (isset(self::$add_attributes[$name])) {

                    $this->data .= ' '.self::$add_attributes[$name].' ';
                }

                // If img or br, we don't close it here
                if ($name !== 'img' && $name !== 'br') $this->data .= '>';
            }
        }

        if (in_array($name, self::$blacklist_tags)) {
            $this->strip_content = true;
        }

        $this->empty_tags[] = $empty_tag;
    }


    public function endTag($parser, $name)
    {
        if (! array_pop($this->empty_tags) && $this->isAllowedTag($name)) {
            $this->data .= $name !== 'img' && $name !== 'br' ? '</'.$name.'>' : '/>';
        }

        if ($this->is_code && $name === 'pre') $this->is_code = false;
    }


    public function dataTag($parser, $content)
    {
        $content = str_replace("\xc2\xa0", ' ', $content); // Replace &nbsp; with normal space

        // Issue with Cyrillic characters
        // Replace mutliple space by a single one
        // if (! $this->is_code) {
        //     $content = preg_replace('!\s+!', ' ', $content);
        // }

        if (! $this->strip_content) {
            $this->data .= htmlspecialchars($content, ENT_QUOTES, 'UTF-8', false);
        }
    }


    public function getAbsoluteUrl($path, $url)
    {
        $components = parse_url($url);

        if (! isset($components['scheme'])) $components['scheme'] = 'http';

        if (! isset($components['host'])) {

            if ($url) {

                $components['host'] = $url;
                $components['path'] = '/';
            }
            else {

                return '';
            }
        }

        if ($path{0} === '/') {

            // Absolute path
            return $components['scheme'].'://'.$components['host'].$path;
        }
        else {

            // Relative path
            $url_path = isset($components['path']) && ! empty($components['path']) ? $components['path'] : '/';
            $length = strlen($url_path);

            if ($length > 1 && $url_path{$length - 1} !== '/') {
                $url_path = dirname($url_path).'/';
            }

            if (substr($path, 0, 2) === './') {
                $path = substr($path, 2);
            }

            return $components['scheme'].'://'.$components['host'].$url_path.$path;
        }
    }


    public function isRelativePath($value)
    {
        if (strpos($value, 'data:') === 0) return false;
        return strpos($value, '://') === false && strpos($value, '//') !== 0;
    }


    public function isAllowedTag($name)
    {
        return isset(self::$whitelist_tags[$name]);
    }


    public function isAllowedAttribute($tag, $attribute)
    {
        return in_array($attribute, self::$whitelist_tags[$tag]);
    }


    public function isResource($attribute)
    {
        return in_array($attribute, self::$media_attributes);
    }


    public function isAllowedIframeResource($value)
    {
        foreach (self::$iframe_whitelist as $url) {

            if (strpos($value, $url) === 0) {
                return true;
            }
        }

        return false;
    }


    public function isAllowedProtocol($value)
    {
        foreach (self::$scheme_whitelist as $protocol) {

            if (strpos($value, $protocol) === 0) {
                return true;
            }
        }

        return false;
    }


    public function isBlacklistedMedia($resource)
    {
        foreach (self::$media_blacklist as $name) {

            if (strpos($resource, $name) !== false) {
                return true;
            }
        }

        return false;
    }


    public function isPixelTracker($tag, array $attributes)
    {
        return $tag === 'img' &&
                isset($attributes['height']) && isset($attributes['width']) &&
                $attributes['height'] == 1 && $attributes['width'] == 1;
    }


    public function validateAttributeValue($attribute, $value)
    {
        if (in_array($attribute, self::$integer_attributes)) {
            return ctype_digit($value);
        }

        return true;
    }


    public function removeMultipleTags($data)
    {
        // Replace <br/><br/> by only one
        return preg_replace("/(<br\s*\/?>\s*)+/", "<br/>", $data);
    }


    public function removeEmptyTags($data)
    {
        return preg_replace('/<([^<\/>]*)>([\s]*?|(?R))<\/\1>/imsU', '', $data);
    }


    public function removeHTMLTags($data)
    {
        return preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $data);
    }


    public static function stripXmlTag($data)
    {
        if (strpos($data, '<?xml') !== false) {
            $data = substr($data, strrpos($data, '?>') + 2);
        }

        return $data;
    }


    public static function stripMetaTags($data)
    {
        return preg_replace('/<meta\s.*?\/>/is', '', $data);
    }


    public static function getEncodingFromXmlTag($data)
    {
        $encoding = '';

        if (strpos($data, '<?xml') !== false) {

            $data = substr($data, 0, strrpos($data, '?>'));
            $data = str_replace("'", '"', $data);

            $p1 = strpos($data, 'encoding=');
            $p2 = strpos($data, '"', $p1 + 10);

            $encoding = substr($data, $p1 + 10, $p2 - $p1 - 10);
            $encoding = strtolower($encoding);
        }

        return $encoding;
    }
}
