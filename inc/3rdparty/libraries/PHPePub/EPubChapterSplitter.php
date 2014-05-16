<?php
/**
 * Split an HTML file into smaller html files, retaining the formatting and structure for the individual parts.
 * What this splitter does is using DOM to try and retain any formatting in the file, including rebuilding the DOM tree for subsequent parts.
 * Split size is considered max target size. The actual size is the result of an even split across the resulting files.
 *
 * @author A. Grandt <php@grandt.com>
 * @copyright 2009-2014 A. Grandt
 * @license GNU LGPL 2.1
 * @link http://www.phpclasses.org/package/6115
 * @link https://github.com/Grandt/PHPePub
 * @version 3.20
 */
class EPubChapterSplitter {
    const VERSION = 3.20;

    private $splitDefaultSize = 250000;
    private $bookVersion = EPub::BOOK_VERSION_EPUB2;

    /**
     *
     * Enter description here ...
     *
     * @param unknown_type $ident
     */
    function setVersion($bookVersion) {
        $this->bookVersion = is_string($bookVersion) ? trim($bookVersion) : EPub::BOOK_VERSION_EPUB2;
    }

	/**
     * Set default chapter target size.
     * Default is 250000 bytes, and minimum is 10240 bytes.
     *
     * @param $size segment size in bytes
     * @return void
     */
    function setSplitSize($size) {
        $this->splitDefaultSize = (int)$size;
        if ($size < 10240) {
            $this->splitDefaultSize = 10240; // Making the file smaller than 10k is not a good idea.
        }
    }

    /**
     * Get the chapter target size.
     *
     * @return $size
     */
    function getSplitSize() {
        return $this->splitDefaultSize;
    }

    /**
     * Split $chapter into multiple parts.
     *
     * The search string can either be a regular string or a PHP PECL Regular Expression pattern as defined here: http://www.php.net/manual/en/pcre.pattern.php
     * If the search string is a regular string, the matching will be for lines in the HTML starting with the string given
     *
     * @param String $chapter XHTML file
     * @param Bool   $splitOnSearchString Split on chapter boundaries, Splitting on search strings disables the split size check.
     * @param String $searchString Chapter string to search for can be fixed text, or a regular expression pattern.
     *
     * @return array with 1 or more parts
     */
    function splitChapter($chapter, $splitOnSearchString = false, $searchString = '/^Chapter\\ /i') {
        $chapterData = array();
        $isSearchRegexp = $splitOnSearchString && (preg_match('#^(\D|\S|\W).+\1[imsxeADSUXJu]*$#m', $searchString) == 1);
        if ($splitOnSearchString && !$isSearchRegexp) {
            $searchString = '#^<.+?>' . preg_quote($searchString, '#') . "#";
        }

        if (!$splitOnSearchString && strlen($chapter) <= $this->splitDefaultSize) {
            return array($chapter);
        }

        $xmlDoc = new DOMDocument();
        @$xmlDoc->loadHTML($chapter);

        $head = $xmlDoc->getElementsByTagName("head");
        $body = $xmlDoc->getElementsByTagName("body");

        $htmlPos = stripos($chapter, "<html");
        $htmlEndPos = stripos($chapter, ">", $htmlPos);
        $newXML = substr($chapter, 0, $htmlEndPos+1) . "\n</html>";
        if (strpos(trim($newXML), "<?xml ") === FALSE) {
            $newXML = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n" . $newXML;
        }
        $headerLength = strlen($newXML);

        $files = array();
        $chapterNames = array();
        $domDepth = 0;
        $domPath = array();
        $domClonedPath = array();

        $curFile = $xmlDoc->createDocumentFragment();
        $files[] = $curFile;
        $curParent = $curFile;
        $curSize = 0;

        $bodyLen = strlen($xmlDoc->saveXML($body->item(0)));
        $headLen = strlen($xmlDoc->saveXML($head->item(0))) + $headerLength;

        $partSize = $this->splitDefaultSize - $headLen;

        if ($bodyLen > $partSize) {
            $parts = ceil($bodyLen / $partSize);
            $partSize = ($bodyLen / $parts)  - $headLen;
        }

        $node = $body->item(0)->firstChild;

        do {
            $nodeData = $xmlDoc->saveXML($node);
            $nodeLen = strlen($nodeData);

            if ($nodeLen > $partSize && $node->hasChildNodes()) {
                $domPath[] = $node;
                $domClonedPath[] = $node->cloneNode(false);
                $domDepth++;

                $node = $node->firstChild;
            }

            $node2 = $node->nextSibling;

            if ($node != null && $node->nodeName != "#text") {
                $doSplit = false;
                if ($splitOnSearchString) {
                    $doSplit = preg_match($searchString, $nodeData) == 1;
                    if ($doSplit) {
                        $chapterNames[] = trim($nodeData);
                    }
                }

                if ($curSize > 0 && ($doSplit || (!$splitOnSearchString && $curSize + $nodeLen > $partSize))) {
                    $curFile = $xmlDoc->createDocumentFragment();
                    $files[] = $curFile;
                    $curParent = $curFile;
                    if ($domDepth > 0) {
                        reset($domPath);
                        reset($domClonedPath);
                        $oneDomClonedPath = each($domClonedPath);
                        while ($oneDomClonedPath) {
                            list($k, $v) = $oneDomClonedPath;
                            $newParent = $v->cloneNode(false);
                            $curParent->appendChild($newParent);
                            $curParent = $newParent;
                            $oneDomClonedPath = each($domClonedPath);
                        }
                    }
                    $curSize = strlen($xmlDoc->saveXML($curFile));
                }
                $curParent->appendChild($node->cloneNode(true));
                $curSize += $nodeLen;
            }

            $node = $node2;
            while ($node == null && $domDepth > 0) {
                $domDepth--;
                $node = end($domPath)->nextSibling;
                array_pop($domPath);
                array_pop($domClonedPath);
                $curParent = $curParent->parentNode;
            }
        } while ($node != null);

        $curFile = null;
        $curSize = 0;

        $xml = new DOMDocument('1.0', $xmlDoc->xmlEncoding);
        $xml->lookupPrefix("http://www.w3.org/1999/xhtml");
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;

        for ($idx = 0; $idx < count($files); $idx++) {
            $xml2Doc = new DOMDocument('1.0', $xmlDoc->xmlEncoding);
            $xml2Doc->lookupPrefix("http://www.w3.org/1999/xhtml");
            $xml2Doc->loadXML($newXML);
            $html = $xml2Doc->getElementsByTagName("html")->item(0);
            $html->appendChild($xml2Doc->importNode($head->item(0), true));
            $body = $xml2Doc->createElement("body");
            $html->appendChild($body);
            $body->appendChild($xml2Doc->importNode($files[$idx], true));

            // force pretty printing and correct formatting, should not be needed, but it is.
            $xml->loadXML($xml2Doc->saveXML());

			$doc = $xml->saveXML();

			if ($this->bookVersion === EPub::BOOK_VERSION_EPUB3) {
				$doc = preg_replace('#^\s*<!DOCTYPE\ .+?>\s*#im', '', $doc);
			}

            $chapterData[$splitOnSearchString ? $chapterNames[$idx] : $idx] = $doc;
        }

        return $chapterData;
    }
}
?>
