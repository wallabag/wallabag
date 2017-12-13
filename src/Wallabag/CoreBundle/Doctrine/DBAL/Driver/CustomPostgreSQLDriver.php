<?php

namespace Wallabag\CoreBundle\Doctrine\DBAL\Driver;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOPgSql\Driver;
use Wallabag\CoreBundle\Doctrine\DBAL\Schema\CustomPostgreSqlSchemaManager;

/**
 * This custom driver allow to use a different schema manager
 * So we can fix the PostgreSQL 10 problem.
 *
 * @see https://github.com/wallabag/wallabag/issues/3479
 * @see https://github.com/doctrine/dbal/issues/2868
 */
class CustomPostgreSQLDriver extends Driver
{
    /**
     * {@inheritdoc}
     */
    public function getSchemaManager(Connection $conn)
    {
        return new CustomPostgreSqlSchemaManager($conn);
    }
}
