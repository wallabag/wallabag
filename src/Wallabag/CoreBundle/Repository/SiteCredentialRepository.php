<?php

namespace Wallabag\CoreBundle\Repository;

/**
 * SiteCredentialRepository.
 */
class SiteCredentialRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Retrieve one username/password for the given host and userId.
     *
     * @param string $host
     * @param int    $userId
     *
     * @return null|array
     */
    public function findOneByHostAndUser($host, $userId)
    {
        return $this->createQueryBuilder('s')
            ->select('s.username', 's.password')
            ->where('s.host = :hostname')->setParameter('hostname', $host)
            ->andWhere('s.user = :userId')->setParameter('userId', $userId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
