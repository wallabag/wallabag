<?php

namespace Wallabag\Util;

class WallabagReadability extends Readability
{
    /**
    * Get the article title as an H1.
    *
    * @return DOMElement
    */
    protected function getArticleTitle() {
        $curTitle = '';
        $origTitle = '';

        try {
            $curTitle = $origTitle = $this->getInnerText($this->dom->getElementsByTagName('title')->item(0));
        } catch(Exception $e) {}

        if (preg_match('/ [\|\-] /', $curTitle))
        {
            $curTitle = preg_replace('/(.*)[\|\-] .*/i', '$1', $origTitle);

            if (count(explode(' ', $curTitle)) < 3) {
                $curTitle = preg_replace('/[^\|\-]*[\|\-](.*)/i', '$1', $origTitle);
            }
        }
        else if(strlen($curTitle) > 150 || strlen($curTitle) < 15)
        {
            $hOnes = $this->dom->getElementsByTagName('h1');
            if($hOnes->length == 1)
            {
                $curTitle = $this->getInnerText($hOnes->item(0));
            }
        }

        $curTitle = trim($curTitle);

        if (count(explode(' ', $curTitle)) <= 4) {
            $curTitle = $origTitle;
        }

        $articleTitle = $this->dom->createElement('h1');
        $articleTitle->innerHTML = $curTitle;

        return $articleTitle;
    }
}
