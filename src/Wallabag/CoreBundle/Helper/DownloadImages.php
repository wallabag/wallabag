<?php

namespace Wallabag\CoreBundle\Helper;

use Psr\Log\LoggerInterface as Logger;
use Symfony\Component\DomCrawler\Crawler;

define('REGENERATE_PICTURES_QUALITY', 75);

class DownloadImages {
    private $folder;
    private $url;
    private $html;
    private $fileName;
    private $logger;

    public function __construct($html, $url, Logger $logger) {
        $this->html = $html;
        $this->url = $url;
        $this->setFolder();
        $this->logger = $logger;
    }

    public function setFolder($folder = "assets/images") {
        // if folder doesn't exist, attempt to create one and store the folder name in property $folder
        if(!file_exists($folder)) {
            mkdir($folder);
        }
        $this->folder = $folder;
    }

    public function process() {
        //instantiate the symfony DomCrawler Component
        $crawler = new Crawler($this->html);
        // create an array of all scrapped image links
        $this->logger->log('debug', 'Finding images inside document');
        $result = $crawler
            ->filterXpath('//img')
            ->extract(array('src'));

        // download and save the image to the folder
        foreach ($result as $image) {
            $file = file_get_contents($image);

            // Checks
            $absolute_path = self::getAbsoluteLink($image, $this->url);
            $filename = basename(parse_url($absolute_path, PHP_URL_PATH));
            $fullpath = $this->folder."/".$filename;
            self::checks($file, $fullpath, $absolute_path);
            $this->html = str_replace($image, $fullpath, $this->html);
        }

        return $this->html;
    }

    private function checks($rawdata, $fullpath, $absolute_path) {
        $fullpath = urldecode($fullpath);

        if (file_exists($fullpath)) {
            unlink($fullpath);
        }

        // check extension
        $this->logger->log('debug','Checking extension');

        $file_ext = strrchr($fullpath, '.');
        $whitelist = array('.jpg', '.jpeg', '.gif', '.png');
        if (!(in_array($file_ext, $whitelist))) {
            $this->logger->log('debug','processed image with not allowed extension. Skipping '.$fullpath);

            return false;
        }

        // check headers
        $this->logger->log('debug','Checking headers');
        $imageinfo = getimagesize($absolute_path);
        if ($imageinfo['mime'] != 'image/gif' && $imageinfo['mime'] != 'image/jpeg' && $imageinfo['mime'] != 'image/jpg' && $imageinfo['mime'] != 'image/png') {
            $this->logger->log('debug','processed image with bad header. Skipping '.$fullpath);

            return false;
        }

        // regenerate image
        $this->logger->log('debug','regenerating image');
        $im = imagecreatefromstring($rawdata);
        if ($im === false) {
            $this->logger->log('error','error while regenerating image '.$fullpath);

            return false;
        }

        switch ($imageinfo['mime']) {
            case 'image/gif':
                $result = imagegif($im, $fullpath);
                $this->logger->log('debug','Re-creating gif');
                break;
            case 'image/jpeg':
            case 'image/jpg':
                $result = imagejpeg($im, $fullpath, REGENERATE_PICTURES_QUALITY);
                $this->logger->log('debug','Re-creating jpg');
                break;
            case 'image/png':
                $this->logger->log('debug','Re-creating png');
                $result = imagepng($im, $fullpath, ceil(REGENERATE_PICTURES_QUALITY / 100 * 9));
                break;
        }
        imagedestroy($im);

        return $result;
    }

    private static function getAbsoluteLink($relativeLink, $url)
    {
        /* return if already absolute URL */
        if (parse_url($relativeLink, PHP_URL_SCHEME) != '') {
            return $relativeLink;
        }

        /* queries and anchors */
        if ($relativeLink[0] == '#' || $relativeLink[0] == '?') {
            return $url.$relativeLink;
        }

        /* parse base URL and convert to local variables:
           $scheme, $host, $path */
        extract(parse_url($url));

        /* remove non-directory element from path */
        $path = preg_replace('#/[^/]*$#', '', $path);

        /* destroy path if relative url points to root */
        if ($relativeLink[0] == '/') {
            $path = '';
        }

        /* dirty absolute URL */
        $abs = $host.$path.'/'.$relativeLink;

        /* replace '//' or '/./' or '/foo/../' with '/' */
        $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
        for ($n = 1; $n > 0; $abs = preg_replace($re, '/', $abs, -1, $n)) {
        }

        /* absolute URL is ready! */
        return $scheme.'://'.$abs;
    }
}
