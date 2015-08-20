<?php

namespace Wallabag\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wallabag\CoreBundle\Entity\User;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $userAdmin = new User();
        $userAdmin->setName('Big boss');
        $userAdmin->setEmail('bigboss@wallabag.org');
        $userAdmin->setUsername('admin');
        $userAdmin->setPassword('mypassword');

        $manager->persist($userAdmin);

        $this->addReference('admin-user', $userAdmin);

        $bobUser = new User();
        $bobUser->setName('Bobby');
        $bobUser->setEmail('bobby@wallabag.org');
        $bobUser->setUsername('bob');
        $bobUser->setPassword('mypassword');

        $manager->persist($bobUser);

        $this->addReference('bob-user', $bobUser);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 10;
    }
}
