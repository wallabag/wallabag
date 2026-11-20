<?php

namespace Wallabag\Helper;

use PHPePub\Core\EPub;

class EpubContentCleaner
{
    public function __construct(
        private readonly InstanceImageLocator $imageLocator,
    ) {
    }

    /**
     * Return the content as XHTML, with the images stored by wallabag added to the book.
     * Nothing is fetched: other references are left untouched.
     */
    public function clean(EPub $book, ?string $content): string
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

        foreach ($xpath->query('.//img', $body) as $image) {
            \assert($image instanceof \DOMElement);

            $localImage = $this->imageLocator->locate($image->getAttribute('src'));

            if (null === $localImage) {
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
