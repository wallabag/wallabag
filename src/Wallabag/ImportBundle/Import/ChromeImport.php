<?php

namespace Wallabag\ImportBundle\Import;

class ChromeImport extends BrowserImport
{
    protected $filepath;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Chrome';
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return 'import_chrome';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'import.chrome.description';
    }
}
