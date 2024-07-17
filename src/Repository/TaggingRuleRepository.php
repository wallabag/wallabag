<?php

namespace Wallabag\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Wallabag\Entity\TaggingRule;

class TaggingRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaggingRule::class);
    }

    /**
     * Remove all tagging rules for a config.
     * Used when a user wants to reset.
     *
     * @param int $configId
     */
    public function removeAllByConfigId($configId)
    {
        $this->getEntityManager()
            ->createQuery('DELETE FROM Wallabag\Entity\TaggingRule tr WHERE tr.config = :configId')
            ->setParameter(':configId', $configId)
            ->execute();
    }
}
