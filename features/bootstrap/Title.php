<?php

use Behat\Mink\Session;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Title extends Page
{
    protected $path = '/quickstart';
    protected $titleXPath = '//title';

    /**
     * check title of different page.
     *
     * @param Session $session
     *
     * @return string
     */
    public function checkTitle(Session $session)
    {
        $pageHeading = $session->getPage()->find('xpath', $this->titleXPath);
        $title = trim($pageHeading->getHtml());
        $title = str_replace("\n", '', $title);

        return $title;
    }
}
