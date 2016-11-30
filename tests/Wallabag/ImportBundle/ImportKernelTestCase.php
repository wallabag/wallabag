<?php

namespace tests\Wallabag\ImportBundle;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class ImportKernelTestCase extends KernelTestCase
{
    protected $fetchingErrorMessage;

    public function setUp()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();
        $this->fetchingErrorMessage = $container->getParameter('wallabag_core.fetching_error_message');
    }
}
