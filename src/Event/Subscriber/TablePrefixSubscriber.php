<?php

namespace Wallabag\Event\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Puts a prefix to each table.
 * This way were used instead of using the built-in strategy from Doctrine, using `naming_strategy`
 * Because it conflicts with the DefaultQuoteStrategy (that escape table name, like user for Postgres)
 * see #1498 for more detail.
 *
 * Solution from :
 *      - http://stackoverflow.com/a/23860613/569101
 *      - http://doctrine-orm.readthedocs.org/en/latest/reference/namingstrategy.html
 */
class TablePrefixSubscriber implements EventSubscriber
{
    protected $tablePrefix = '';

    public function __construct($tablePrefix)
    {
        $this->tablePrefix = (string) $tablePrefix;
    }

    public function getSubscribedEvents(): array
    {
        return ['loadClassMetadata'];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $classMetadata = $args->getClassMetadata();

        // if we are in an inheritance hierarchy, only apply this once
        if ($classMetadata->isInheritanceTypeSingleTable() && !$classMetadata->isRootEntity()) {
            return;
        }

        $classMetadata->setPrimaryTable(['name' => $this->tablePrefix . $classMetadata->getTableName()]);

        foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
            if (ClassMetadataInfo::MANY_TO_MANY === $mapping['type'] && isset($classMetadata->associationMappings[$fieldName]['joinTable']['name'])) {
                $mappedTableName = $classMetadata->associationMappings[$fieldName]['joinTable']['name'];
                $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $this->tablePrefix . $mappedTableName;
            }
        }
    }
}
