<?php

namespace Wallabag\CoreBundle\Helper;

use PHPePub\Core\EPub;

class EpubContentCleaner
{
    public const MAX_REMOTE_IMAGES = 100;
    public const MAX_REMOTE_IMAGES_DURATION = 15;

    private $imageLocator;
    private $downloadImages;

    public function __construct(InstanceImageLocator $imageLocator, DownloadImages $downloadImages)
    {
        $this->imageLocator = $imageLocator;
        $this->downloadImages = $downloadImages;
    }

    /**
     * Return the content as XHTML, with the images added to the book: the ones stored by wallabag,
     * then the remote ones, downloaded with the protection against requests to private networks.
     * The other references are left untouched.
     */
    public function clean(EPub $book, ?string $content, ?string $baseUrl = null, ?float $remoteImagesDeadline = null): string
    {
        $document = new \DOMDocument();
        $previousErrors = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><html><body>' . $content . '</body></html>', \LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        $body = $document->getElementsByTagName('body')->item(0);
        $xpath = new \DOMXPath($document);

        foreach (iterator_to_array($xpath->query('.//comment()', $body)) as $comment) {
            $comment->parentNode->removeChild($comment);
        }

        $remoteImages = [];
        $remoteImagesTried = 0;

        foreach ($xpath->query('.//img', $body) as $image) {
            \assert($image instanceof \DOMElement);

            $localImage = $this->imageLocator->locate($image->getAttribute('src'));

            if (null === $localImage) {
                $source = trim($image->getAttribute('src'));

                if ('' === $source || 0 === stripos($source, 'data:')) {
                    continue;
                }

                if (!\array_key_exists($source, $remoteImages)) {
                    if (self::MAX_REMOTE_IMAGES <= $remoteImagesTried || (null !== $remoteImagesDeadline && microtime(true) >= $remoteImagesDeadline)) {
                        continue;
                    }

                    ++$remoteImagesTried;
                    $remoteImages[$source] = $this->downloadImages->fetchImage($source, $baseUrl);

                    if (null !== $remoteImages[$source]) {
                        $remoteImages[$source]['fileName'] = 'images/' . sha1($source) . '.' . $remoteImages[$source]['extension'];
                        $book->addFile($remoteImages[$source]['fileName'], 'image-' . sha1($source), $remoteImages[$source]['content'], $remoteImages[$source]['mimetype']);
                    }
                }

                if (null !== $remoteImages[$source]) {
                    $image->setAttribute('src', $remoteImages[$source]['fileName']);
                    $image->removeAttribute('srcset');
                }

                continue;
            }

            $fileName = 'images/' . sha1($localImage['path']) . '.' . $localImage['extension'];
            $book->addFile($fileName, 'image-' . sha1($localImage['path']), file_get_contents($localImage['path']), $localImage['mimetype']);

            $image->setAttribute('src', $fileName);
            $image->removeAttribute('srcset');
        }

        $xhtml = '';
        foreach ($body->childNodes as $node) {
            $xhtml .= $document->saveXML($node);
        }

        return $xhtml;
    }
}
