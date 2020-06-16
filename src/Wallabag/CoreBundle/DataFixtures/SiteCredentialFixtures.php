<?php

namespace Wallabag\CoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Wallabag\CoreBundle\Entity\SiteCredential;
use Wallabag\UserBundle\DataFixtures\UserFixtures;

class SiteCredentialFixtures extends Fixture implements DependentFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager): void
    {
        $credential = new SiteCredential($this->getReference('admin-user'));
        $credential->setHost('.super.com');
        $credential->setUsername($this->container->get('wallabag_core.helper.crypto_proxy')->crypt('.super'));
        $credential->setPassword($this->container->get('wallabag_core.helper.crypto_proxy')->crypt('bar'));

        $manager->persist($credential);

        $credential = new SiteCredential($this->getReference('admin-user'));
        $credential->setHost('paywall.example.com');
        $credential->setUsername($this->container->get('wallabag_core.helper.crypto_proxy')->crypt('paywall.example'));
        $credential->setPassword($this->container->get('wallabag_core.helper.crypto_proxy')->crypt('bar'));

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
