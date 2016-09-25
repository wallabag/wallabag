<?php

namespace Wallabag\ImportBundle\Import;

class FirefoxImport extends BrowserImport
{
    protected $filepath;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Firefox';
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return 'import_firefox';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'import.firefox.description';
    }
}
