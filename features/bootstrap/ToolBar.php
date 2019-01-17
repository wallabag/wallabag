<?php

use Behat\Mink\Element\DocumentElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class ToolBar extends Page
{
    protected $footerBarXPath = "//div[contains(@style,'display: block') and contains(@id,'sfToolbarMainContent')]";
    protected $footerHideButton = "//a[contains(@class,'hide-button')]";

    /**
     * check title of quickstart page.
     *
     * @param DocumentElement $page
     */
    public function hideToolBar(DocumentElement $page)
    {
        $footerBar = $page->find('xpath', $this->footerBarXPath);
        if (null !== $footerBar) {
            $page->find('xpath', $this->footerHideButton)->click();
        }
    }
}
