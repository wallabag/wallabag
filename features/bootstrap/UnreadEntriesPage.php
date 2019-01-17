<?php

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class UnreadEntriesPage extends Page
{
    protected $path = '/unread/list';
    protected $unreadCountXPath = '//ul[@id="slide-out"]//a[contains(text(),"Unread")]/span';
    protected $listEntriesPath = "//ul[contains(@class,'collection')]/li[contains(@class,'col')]";
    protected $gridEntriesPath = "//ul[contains(@class,'row data')]/li[contains(@class,'col')]";
    protected $titleXpath = "//a[contains(@class,'card-title')]";
    protected $descriptionXpath = "//a[contains(@class,'grey-text')]";
    protected $navBtnID = 'nav-btn-add';
    protected $entryUrl = 'entry_url';
    protected $addButton = 'add';
    protected $deletexpath = "//a[contains(@title,'Delete')]";

    /**
     * count number of unread entries present.
     *
     * @param Session $session
     *
     * @return string
     */
    public function countUnreadEntry(Session $session)
    {
        $page = $session->getPage();

        return $page->find('xpath', $this->unreadCountXPath)->getHtml();
    }

    /**
     * check if their is entry listed or not.
     *
     * @param Session $session
     * @param string  $title
     * @param string  $description
     *
     * @return bool
     */
    public function isEntryListed(Session $session, $title, $description)
    {
        $allEntry = $this->getAllEntry($session);
        foreach ($allEntry as $entry) {
            if ($entry->find('xpath', $this->titleXpath)->getText() === $title
                && $entry->find('xpath', $this->descriptionXpath)->getText() === $description) {
                return true;
            }
        }

        return false;
    }

    /**
     * delete entry which title is provided.
     *
     * @param Session $session
     * @param string  $title
     */
    public function deleteEntry(Session $session, $title)
    {
        $Allentry = $this->getAllEntry($session);
        foreach ($Allentry as $entry) {
            if ($entry->find('xpath', $this->titleXpath)->getText() === $title) {
                $entry->mouseOver();
                $entry->find('xpath', $this->deletexpath)->click();
                $this->getDriver()->getWebDriverSession()->accept_alert();
            }
        }
    }

    /**
     * Cancel delete operation.
     *
     * @param Session $session
     * @param string  $title
     */
    public function cancelDelete(Session $session, $title)
    {
        $Allentry = $this->getAllEntry($session);
        foreach ($Allentry as $entry) {
            if ($entry->find('xpath', $this->titleXpath)->getText() === $title) {
                $entry->mouseOver();
                $entry->find('xpath', $this->deletexpath)->click();
                $this->getDriver()->getWebDriverSession()->dismiss_alert();
            }
        }
    }

    /**
     * add new entry to list of unread entries.
     *
     * @param Session $session
     * @param string  $link
     */
    public function addNewEntry(Session $session, $link)
    {
        $page = $session->getPage();
        $page->findById($this->navBtnID)->click();
        $page->fillField($this->entryUrl, $link);
        $page->findButton($this->addButton)->click();
    }

    /**
     * get out all of entries present at unreadentries.
     *
     * @param Session $session
     *
     * @return NodeElement[]
     */
    private function getAllEntry($session)
    {
        $page = $session->getPage();
        $Allentry = $page->findAll('xpath', $this->listEntriesPath);
        if (empty($Allentry)) {
            $Allentry = $page->findAll('xpath', $this->gridEntriesPath);
        }

        return $Allentry;
    }
}
