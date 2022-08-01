<?php

namespace Wallabag\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Wallabag\CoreBundle\Entity\Config;

/**
 * @method Config|null findOneByUser(int $userId)
 */
class ConfigRepository extends EntityRepository
{
}
