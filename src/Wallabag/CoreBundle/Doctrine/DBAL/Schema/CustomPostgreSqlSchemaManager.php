<?php

namespace Wallabag\CoreBundle\Doctrine\DBAL\Schema;

use Doctrine\DBAL\Schema\PostgreSqlSchemaManager;
use Doctrine\DBAL\Schema\Sequence;

/**
 * This custom schema manager fix the PostgreSQL 10 problem.
 *
 * @see https://github.com/wallabag/wallabag/issues/3479
 * @see https://github.com/doctrine/dbal/issues/2868
 */
class CustomPostgreSqlSchemaManager extends PostgreSqlSchemaManager
{
    /**
     * {@inheritdoc}
     */
    protected function _getPortableSequenceDefinition($sequence)
    {
        $sequenceName = $sequence['relname'];
        if ('public' !== $sequence['schemaname']) {
            $sequenceName = $sequence['schemaname'] . '.' . $sequence['relname'];
        }

        $query = 'SELECT min_value, increment_by FROM ' . $this->_platform->quoteIdentifier($sequenceName);

        // the `method_exists` is only to avoid test to fail:
        // DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticConnection doesn't support the `getServerVersion`
        if (method_exists($this->_conn->getWrappedConnection(), 'getServerVersion') && (float) ($this->_conn->getWrappedConnection()->getServerVersion()) >= 10) {
            $query = "SELECT min_value, increment_by FROM pg_sequences WHERE schemaname = 'public' AND sequencename = " . $this->_conn->quote($sequenceName);
        }

        $data = $this->_conn->fetchAll($query);

        return new Sequence($sequenceName, $data[0]['increment_by'], $data[0]['min_value']);
    }
}
