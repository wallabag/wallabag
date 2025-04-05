<?php

namespace Wallabag\Doctrine;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

abstract class WallabagMigration extends AbstractMigration
{
    public const UN_ESCAPED_TABLE = true;

    protected string $tablePrefix;
    protected array $defaultIgnoreOriginInstanceRules;
    protected string $fetchingErrorMessage;

    // because there are declared as abstract in `AbstractMigration` we need to delarer here too
    public function up(Schema $schema): void
    {
    }

    public function down(Schema $schema): void
    {
    }

    /**
     * @todo remove when upgrading DoctrineMigration (only needed for PHP 8)
     *
     * @see https://github.com/doctrine/DoctrineMigrationsBundle/issues/393
     */
    public function isTransactional(): bool
    {
        return false;
    }

    public function setTablePrefix(string $tablePrefix): void
    {
        $this->tablePrefix = $tablePrefix;
    }

    public function setDefaultIgnoreOriginInstanceRules(array $defaultIgnoreOriginInstanceRules): void
    {
        $this->defaultIgnoreOriginInstanceRules = $defaultIgnoreOriginInstanceRules;
    }

    public function setFetchingErrorMessage(string $fetchingErrorMessage): void
    {
        $this->fetchingErrorMessage = $fetchingErrorMessage;
    }

    protected function getTable($tableName, $unEscaped = false)
    {
        $table = $this->tablePrefix . $tableName;

        if (self::UN_ESCAPED_TABLE === $unEscaped) {
            return $table;
        }

        // escape table name is handled using " on postgresql
        if ($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            return '"' . $table . '"';
        }

        // return escaped table
        return '`' . $table . '`';
    }

    protected function getForeignKeyName(string $tableName, string $foreignColumnName): string
    {
        return $this->generateIdentifierName([$this->getTable($tableName, true), $foreignColumnName], 'fk');
    }

    protected function getIndexName(string $tableName, $indexedColumnNames): string
    {
        $indexedColumnNames = (array) $indexedColumnNames;

        return $this->generateIdentifierName(array_merge([$this->getTable($tableName, true)], $indexedColumnNames), 'idx');
    }

    protected function getUniqueIndexName(string $tableName, string $indexedColumnName): string
    {
        return $this->generateIdentifierName([$this->getTable($tableName, true), $indexedColumnName], 'uniq');
    }

    /**
     * @see \Doctrine\DBAL\Schema\AbstractAsset::_generateIdentifierName
     *
     * @param string[] $columnNames
     */
    protected function generateIdentifierName(array $columnNames, string $prefix = ''): string
    {
        $hash = implode('', array_map(static fn ($column): string => dechex(crc32($column)), $columnNames));

        return strtoupper(substr($prefix . '_' . $hash, 0, $this->platform->getMaxIdentifierLength()));
    }
}
