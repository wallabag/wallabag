<?php

namespace Wallabag\CoreBundle\Helper;

use Graby\Graby;
use Psr\Log\LoggerInterface as Logger;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Tools\Utils;
use Wallabag\CoreBundle\Repository\TagRepository;

/**
 * This kind of proxy class take care of getting the content from an url
 * and update the entry with what it found.
 */
class ContentProxy
{
    protected $graby;
    protected $tagger;
    protected $logger;
    protected $tagRepository;

    public function __construct(Graby $graby, RuleBasedTagger $tagger, TagRepository $tagRepository, Logger $logger)
    {
        $this->graby = $graby;
        $this->tagger = $tagger;
        $this->logger = $logger;
        $this->tagRepository = $tagRepository;
    }

    /**
     * Fetch content using graby and hydrate given entry with results information.
     * In case we couldn't find content, we'll try to use Open Graph data.
     *
     * We can also force the content, in case of an import from the v1 for example, so the function won't
     * fetch the content from the website but rather use information given with the $content parameter.
     *
     * @param Entry  $entry   Entry to update
     * @param string $url     Url to grab content for
     * @param array  $content An array with AT LEAST keys title, html, url, language & content_type to skip the fetchContent from the url
     *
     * @return Entry
     */
    public function updateEntry(Entry $entry, $url, array $content = [])
    {
        // do we have to fetch the content or the provided one is ok?
        if (empty($content) || false === $this->validateContent($content)) {
            $content = $this->graby->fetchContent($url);
        }

        $title = $content['title'];
        if (!$title && isset($content['open_graph']['og_title'])) {
            $title = $content['open_graph']['og_title'];
        }

        $html = $content['html'];
        if (false === $html) {
            $html = '<p>Unable to retrieve readable content.</p>';

            if (isset($content['open_graph']['og_description'])) {
                $html .= '<p><i>But we found a short description: </i></p>';
                $html .= $content['open_graph']['og_description'];
            }
        }

        $entry->setUrl($content['url'] ?: $url);
        $entry->setTitle($title);
        $entry->setContent($html);
        $entry->setLanguage($content['language']);
        $entry->setMimetype($content['content_type']);
        $entry->setReadingTime(Utils::getReadingTime($html));

        $domainName = parse_url($entry->getUrl(), PHP_URL_HOST);
        if (false !== $domainName) {
            $entry->setDomainName($domainName);
        }

        if (isset($content['open_graph']['og_image'])) {
            $entry->setPreviewPicture($content['open_graph']['og_image']);
        }

        try {
            $this->tagger->tag($entry);
        } catch (\Exception $e) {
            $this->logger->error('Error while trying to automatically tag an entry.', [
                'entry_url' => $url,
                'error_msg' => $e->getMessage(),
            ]);
        }

        return $entry;
    }

    /**
     * Assign some tags to an entry.
     *
     * @param Entry        $entry
     * @param array|string $tags          An array of tag or a string coma separated of tag
     * @param array        $entitiesReady Entities from the EntityManager which are persisted but not yet flushed
     *                                    It is mostly to fix duplicate tag on import @see http://stackoverflow.com/a/7879164/569101
     */
    public function assignTagsToEntry(Entry $entry, $tags, array $entitiesReady = [])
    {
        if (!is_array($tags)) {
            $tags = explode(',', $tags);
        }

        // keeps only Tag entity from the "not yet flushed entities"
        $tagsNotYetFlushed = [];
        foreach ($entitiesReady as $entity) {
            if ($entity instanceof Tag) {
                $tagsNotYetFlushed[$entity->getLabel()] = $entity;
            }
        }

        foreach ($tags as $label) {
            $label = trim($label);

            // avoid empty tag
            if (0 === strlen($label)) {
                continue;
            }

            if (isset($tagsNotYetFlushed[$label])) {
                $tagEntity = $tagsNotYetFlushed[$label];
            } else {
                $tagEntity = $this->tagRepository->findOneByLabel($label);

                if (is_null($tagEntity)) {
                    $tagEntity = new Tag();
                    $tagEntity->setLabel($label);
                }
            }

            // only add the tag on the entry if the relation doesn't exist
            if (false === $entry->getTags()->contains($tagEntity)) {
                $entry->addTag($tagEntity);
            }
        }
    }

    /**
     * Validate that the given content as enough value to be used
     * instead of fetch the content from the url.
     *
     * @param array $content
     *
     * @return bool true if valid otherwise false
     */
    private function validateContent(array $content)
    {
        return isset($content['title']) && isset($content['html']) && isset($content['url']) && isset($content['language']) && isset($content['content_type']);
    }

    /**
     * Changing pictures URL in article content.
     */
    public static function filterPicture($content, $url, $id)
    {
        $matches = array();
        $processing_pictures = array(); // list of processing image to avoid processing the same pictures twice
        preg_match_all('#<\s*(img)[^>]+src="([^"]*)"[^>]*>#Si', $content, $matches, PREG_SET_ORDER);
        foreach ($matches as $i => $link) {
            $link[1] = trim($link[1]);
            if (!preg_match('#^(([a-z]+://)|(\#))#', $link[1])) {
                $absolute_path = self::_getAbsoluteLink($link[2], $url);
                $filename = basename(parse_url($absolute_path, PHP_URL_PATH));
                $directory = self::_createAssetsDirectory($id);
                $fullpath = $directory.'/'.$filename;

                if (in_array($absolute_path, $processing_pictures) === true) {
                    // replace picture's URL only if processing is OK : already processing -> go to next picture
                  continue;
                }

                if (self::_downloadPictures($absolute_path, $fullpath) === true) {
                    $content = str_replace($matches[$i][2], Tools::getPocheUrl().$fullpath, $content);
                }

                $processing_pictures[] = $absolute_path;
            }
        }

        return $content;
    }

    /**
     * Get absolute URL.
     */
    private static function _getAbsoluteLink($relativeLink, $url)
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

    /**
     * Downloading pictures.
     *
     * @return bool true if the download and processing is OK, false else
     */
    private static function _downloadPictures($absolute_path, $fullpath)
    {
        $rawdata = Tools::getFile($absolute_path);
        $fullpath = urldecode($fullpath);

        if (file_exists($fullpath)) {
            unlink($fullpath);
        }

        // check extension
        $file_ext = strrchr($fullpath, '.');
        $whitelist = array('.jpg', '.jpeg', '.gif', '.png');
        if (!(in_array($file_ext, $whitelist))) {
            Tools::logm('processed image with not allowed extension. Skipping '.$fullpath);

            return false;
        }

        // check headers
        $imageinfo = getimagesize($absolute_path);
        if ($imageinfo['mime'] != 'image/gif' && $imageinfo['mime'] != 'image/jpeg' && $imageinfo['mime'] != 'image/jpg' && $imageinfo['mime'] != 'image/png') {
            Tools::logm('processed image with bad header. Skipping '.$fullpath);

            return false;
        }

        // regenerate image
        $im = imagecreatefromstring($rawdata);
        if ($im === false) {
            Tools::logm('error while regenerating image '.$fullpath);

            return false;
        }

        switch ($imageinfo['mime']) {
            case 'image/gif':
                $result = imagegif($im, $fullpath);
                break;
            case 'image/jpeg':
            case 'image/jpg':
                $result = imagejpeg($im, $fullpath, REGENERATE_PICTURES_QUALITY);
                break;
            case 'image/png':
                $result = imagepng($im, $fullpath, ceil(REGENERATE_PICTURES_QUALITY / 100 * 9));
                break;
        }
        imagedestroy($im);

        return $result;
    }

    /**
     * Create a directory for an article.
     *
     * @param $id ID of the article
     *
     * @return string
     */
    private static function _createAssetsDirectory($id)
    {
        $assets_path = ABS_PATH;
        if (!is_dir($assets_path)) {
            mkdir($assets_path, 0715);
        }

        $article_directory = $assets_path.$id;
        if (!is_dir($article_directory)) {
            mkdir($article_directory, 0715);
        }

        return $article_directory;
    }

    /**
     * Remove the directory.
     *
     * @param $directory
     *
     * @return bool
     */
    public static function removeDirectory($directory)
    {
        if (is_dir($directory)) {
            $files = array_diff(scandir($directory), array('.', '..'));
            foreach ($files as $file) {
                (is_dir("$directory/$file")) ? self::removeDirectory("$directory/$file") : unlink("$directory/$file");
            }

            return rmdir($directory);
        }
    }
}
