<?php

namespace Wallabag\CoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wallabag\CoreBundle\Entity\SiteCredential;
use Wallabag\UserBundle\DataFixtures\UserFixtures;

class SiteCredentialFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $credential = new SiteCredential($this->getReference('admin-user'));
        $credential->setHost('example.com');
        $credential->setUsername('foo');
        $credential->setPassword('bar');

        $manager->persist($credential);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
