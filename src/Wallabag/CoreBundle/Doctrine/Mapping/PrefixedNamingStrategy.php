<?php

namespace Wallabag\CoreBundle\Doctrine\Mapping;

use Doctrine\ORM\Mapping\NamingStrategy;

/**
 * Puts a prefix to each table.
 *
 * Solution from :
 *      - http://stackoverflow.com/a/23860613/569101
 *      - http://doctrine-orm.readthedocs.org/en/latest/reference/namingstrategy.html
 */
class PrefixedNamingStrategy implements NamingStrategy
{
    protected $prefix = '';

    public function __construct($prefix)
    {
        $this->prefix = (string) $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function classToTableName($className)
    {
        return strtolower($this->prefix.substr($className, strrpos($className, '\\') + 1));
    }

    /**
     * {@inheritdoc}
     */
    public function propertyToColumnName($propertyName, $className = null)
    {
        return $propertyName;
    }

    /**
     * {@inheritdoc}
     */
    public function referenceColumnName()
    {
        return 'id';
    }

    /**
     * {@inheritdoc}
     */
    public function joinColumnName($propertyName)
    {
        return $propertyName.'_'.$this->referenceColumnName();
    }

    /**
     * {@inheritdoc}
     */
    public function joinTableName($sourceEntity, $targetEntity, $propertyName = null)
    {
        // for join table we don't want to have both table concatenated AND prefixed
        // we just want the whole table to prefixed once
        // ie: not "wallabag_entry_wallabag_tag" but "wallabag_entry_tag"
        $target = substr($targetEntity, strrpos($targetEntity, '\\') + 1);

        return strtolower($this->classToTableName($sourceEntity).'_'.$target);
    }

    /**
     * {@inheritdoc}
     */
    public function joinKeyColumnName($entityName, $referencedColumnName = null)
    {
        return strtolower($this->classToTableName($entityName).'_'.($referencedColumnName ?: $this->referenceColumnName()));
    }

    /**
     * {@inheritdoc}
     */
    public function embeddedFieldToColumnName($propertyName, $embeddedColumnName, $className = null, $embeddedClassName = null)
    {
        return $propertyName.'_'.$embeddedColumnName;
    }
}
