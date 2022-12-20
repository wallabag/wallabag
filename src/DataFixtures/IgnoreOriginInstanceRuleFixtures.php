<?php

namespace App\DataFixtures;

use App\Entity\IgnoreOriginInstanceRule;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class IgnoreOriginInstanceRuleFixtures extends Fixture implements ContainerAwareInterface
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
        foreach ($this->container->getParameter('wallabag.default_ignore_origin_instance_rules') as $ignore_origin_instance_rule) {
            $newIgnoreOriginInstanceRule = new IgnoreOriginInstanceRule();
            $newIgnoreOriginInstanceRule->setRule($ignore_origin_instance_rule['rule']);
            $manager->persist($newIgnoreOriginInstanceRule);
        }

        $manager->flush();
    }
}
