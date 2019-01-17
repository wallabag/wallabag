<?php

use Behat\Mink\Session;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class LoginPage extends Page
{
    protected $path = '/login';

    protected $usernameFieldName = 'username';
    protected $passwordFieldName = 'password';
    protected $submitBtnXpath = '//form/div/button';
    protected $toastContainerID = 'toast-container';

    /**
     * logs user in.
     *
     * @param Session $session
     * @param string  $username
     * @param string  $password
     */
    public function login(Session $session, $username, $password)
    {
        $page = $session->getPage();
        $page->fillField($this->usernameFieldName, $username);
        $page->fillField($this->passwordFieldName, $password);
        $page->find('xpath', $this->submitBtnXpath)->click();
    }

    /**
     * get error message.
     *
     * @param Session $session
     *
     * @return string
     */
    public function getError(Session $session)
    {
        return $session->getPage()->findById($this->toastContainerID)->getText();
    }
}
