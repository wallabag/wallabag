<?php

namespace Wallabag\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Wallabag\Entity\User;

abstract class WallabagKernelTestCase extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
    }

    protected function createApplication(): Application
    {
        return new Application(static::$kernel);
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    protected function getUser(string $username): User
    {
        $user = $this->getEntityManager()->getRepository(User::class)->findOneBy(['username' => $username]);
        \assert($user instanceof User);

        return $user;
    }
}
