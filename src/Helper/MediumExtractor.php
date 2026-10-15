<?php

namespace Wallabag\Helper;

use Graby\Graby;

class MediumExtractor
{
    private PublicHttpClient $http;

    public function __construct(PublicHttpClient $http)
    {
        $this->http = $http;
    }

    public function extract(string $url): array
    {
        if (!SpecialGrabbers::mediumUrl($url)) {
            throw new \InvalidArgumentException('Not a supported Medium story URL.');
        }
        try {
            $response = $this->http->get($url);
            $graby = new Graby(['singlepage' => false, 'multipage' => false]);
            $graby->setContentAsPrefetched($response['body']);
            $content = $graby->fetchContent($response['url']);
            if (empty($content['html']) || $content['html'] === $graby->getConfig('error_message')) {
                throw new \RuntimeException('No public readable body.');
            }
            $doc = new \DOMDocument();
            $previous = libxml_use_internal_errors(true);
            $doc->loadHTML('<?xml encoding="UTF-8">' . $content['html'], \LIBXML_NONET);
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            $xpath = new \DOMXPath($doc);
            foreach ($xpath->query('//img|//video|//audio|//source|//iframe|//object|//embed') as $node) {
                $node->parentNode->removeChild($node);
            }
            $html = '';
            foreach ($doc->getElementsByTagName('body')->item(0)->childNodes as $node) {
                $html .= $doc->saveHTML($node);
            }
            $content['html'] = '<p><strong>Public Medium snapshot; completeness unverified.</strong> Only text returned publicly by Medium is saved. Membership-only, truncated or missing sections are not captured. Media are omitted and are not downloaded. Verify the original.</p>' . $html . '<p><a rel="noreferrer noopener" href="' . htmlspecialchars($response['url'], \ENT_QUOTES, 'UTF-8') . '">Original Medium story</a></p>';
            $content['image'] = null;
            $content['headers'] = ['content-type' => 'text/html'];
            $content['url'] = $response['url'];
            $content['status'] = 206;

            return $content;
        } catch (\Throwable $e) {
            return ['title' => 'Medium capture unavailable', 'url' => $url, 'status' => 502, 'html' => '<p><strong>Medium capture unavailable.</strong> The public response could not be extracted. This is a bookmark, not a captured story. No mirror or access restrictions were bypassed.</p>'];
        }
    }
}
