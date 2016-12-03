<?php

namespace tests\Wallabag\CoreBundle;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class CoreKernelTestCase extends KernelTestCase
{
    protected $fetchingErrorMessage;

    public function setUp()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();
        $this->fetchingErrorMessage = $container->getParameter('wallabag_core.fetching_error_message');
    }
}
