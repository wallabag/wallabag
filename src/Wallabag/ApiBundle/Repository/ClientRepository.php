<?php

namespace Wallabag\ApiBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ClientRepository extends EntityRepository
{
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        if (!empty($criteria['id'])) {
            // cast client id to be an integer to avoid postgres error:
            // "invalid input syntax for integer"
            $criteria['id'] = (int) $criteria['id'];
        }

        return parent::findOneBy($criteria, $orderBy);
    }
}
