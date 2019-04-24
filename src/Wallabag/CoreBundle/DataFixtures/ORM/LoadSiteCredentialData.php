<?php

namespace Wallabag\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Wallabag\CoreBundle\Entity\SiteCredential;

class LoadSiteCredentialData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
    public function load(ObjectManager $manager)
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
    public function getOrder()
    {
        return 50;
    }
}
