<?php

namespace PicoFeed;

require_once __DIR__.'/Logging.php';
require_once __DIR__.'/Filter.php';
require_once __DIR__.'/Encoding.php';
require_once __DIR__.'/Grabber.php';

abstract class Parser
{
    protected $content = '';

    public $id = '';
    public $url = '';
    public $title = '';
    public $updated = '';
    public $items = array();
    public $grabber = false;
    public $grabber_ignore_urls = array();
    public $grabber_timeout = null;
    public $grabber_user_agent = null;


    abstract public function execute();


    public function __construct($content, $http_encoding = '')
    {
        $xml_encoding = Filter::getEncodingFromXmlTag($content);
        Logging::log(\get_called_class().': HTTP Encoding "'.$http_encoding.'" ; XML Encoding "'.$xml_encoding.'"');

        // Strip XML tag to avoid multiple encoding/decoding in the next XML processing
        $this->content = Filter::stripXmlTag($content);

        // Encode everything in UTF-8
        if ($xml_encoding == 'windows-1251' || $http_encoding == 'windows-1251') {
            $this->content = Encoding::cp1251ToUtf8($this->content);
        }
        else {
            $this->content = Encoding::toUTF8($this->content);
        }

        // Workarounds
        $this->content = $this->normalizeData($this->content);
    }


    public function filterHtml($item_content, $item_url)
    {
        $content = '';

        if ($this->grabber && ! in_array($item_url, $this->grabber_ignore_urls)) {
            $grabber = new Grabber($item_url);
            $grabber->download($this->grabber_timeout, $this->grabber_user_agent);
            if ($grabber->parse()) $item_content = $grabber->content;
        }

        if ($item_content) {
            $filter = new Filter($item_content, $item_url);
            $content = $filter->execute();
        }

        return $content;
    }


    public function getXmlErrors()
    {
        $errors = array();

        foreach(\libxml_get_errors() as $error) {

            $errors[] = sprintf('XML error: %s (Line: %d - Column: %d - Code: %d)',
                $error->message,
                $error->line,
                $error->column,
                $error->code
            );
        }

        return implode(', ', $errors);
    }


    // Dirty quickfix before XML parsing
    public function normalizeData($data)
    {
        return str_replace("\xc3\x20", '', $data);
    }


    // Trim whitespace from the begining, the end and inside a string and don't break utf-8 string
    public function stripWhiteSpace($value)
    {
        $value = str_replace("\r", "", $value);
        $value = str_replace("\t", "", $value);
        $value = str_replace("\n", "", $value);
        return trim($value);
    }


    public function generateId()
    {
        // crc32b seems to be faster and shorter than other hash algorithms
        return hash('crc32b', implode(func_get_args()));
    }


    public function parseDate($value)
    {
        // Format => truncate to this length if not null
        $formats = array(
            DATE_ATOM => null,
            DATE_RSS => null,
            DATE_COOKIE => null,
            DATE_ISO8601 => null,
            DATE_RFC822 => null,
            DATE_RFC850 => null,
            DATE_RFC1036 => null,
            DATE_RFC1123 => null,
            DATE_RFC2822 => null,
            DATE_RFC3339 => null,
            'D, d M Y H:i:s' => 25,
            'D, d M Y h:i:s' => 25,
            'D M d Y H:i:s' => 24,
            'Y-m-d H:i:s' => 19,
            'Y-m-d\TH:i:s' => 19,
            'd/m/Y H:i:s' => 19,
            'D, d M Y' => 16,
            'Y-m-d' => 10,
            'd-m-Y' => 10,
            'm-d-Y' => 10,
            'd.m.Y' => 10,
            'm.d.Y' => 10,
            'd/m/Y' => 10,
            'm/d/Y' => 10,
        );

        $value = trim($value);

        foreach ($formats as $format => $length) {
            $timestamp = $this->getValidDate($format, substr($value, 0, $length));
            if ($timestamp > 0) return $timestamp;
        }

        return time();
    }


    public function getValidDate($format, $value)
    {
        $date = \DateTime::createFromFormat($format, $value);

        if ($date !== false) {
            $errors = \DateTime::getLastErrors();
            if ($errors['error_count'] === 0 && $errors['warning_count'] === 0) return $date->getTimestamp();
        }

        return 0;
    }
}
