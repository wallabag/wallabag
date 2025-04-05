<?php

namespace Wallabag\Event\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

class SchemaAdapterSubscriber implements EventSubscriber
{
    public function __construct(
        private readonly string $databaseTablePrefix,
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return ['postGenerateSchema'];
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $eventArgs)
    {
        $platform = $eventArgs->getEntityManager()->getConnection()->getDatabasePlatform();

        if (!$platform instanceof MySQLPlatform) {
            return;
        }

        $schema = $eventArgs->getSchema();

        $entryTable = $schema->getTable($this->databaseTablePrefix . 'entry');
        $entryTable->addOption('collate', 'utf8mb4_unicode_ci');
        $entryTable->addOption('charset', 'utf8mb4');

        $tagTable = $schema->getTable($this->databaseTablePrefix . 'tag');
        $tagTable->addOption('collate', 'utf8mb4_bin');
        $tagTable->addOption('charset', 'utf8mb4');

        foreach ($tagTable->getIndexes() as $index) {
            if ($index->getColumns() === ['label']) {
                $tagTable->dropIndex($index->getName());
                $tagTable->addIndex($index->getColumns(), $index->getName(), $index->getFlags(), array_merge(
                    $index->getOptions(),
                    ['lengths' => [255]]
                ));
            }
        }

        $oauth2AccessTokenTable = $schema->getTable($this->databaseTablePrefix . 'oauth2_access_tokens');
        $oauth2AccessTokenTable->modifyColumn('token', ['length' => 191]);
        $oauth2AccessTokenTable->modifyColumn('scope', ['length' => 191]);

        $oauth2AuthCodeTable = $schema->getTable($this->databaseTablePrefix . 'oauth2_auth_codes');
        $oauth2AuthCodeTable->modifyColumn('token', ['length' => 191]);
        $oauth2AuthCodeTable->modifyColumn('scope', ['length' => 191]);

        $oauth2RefreshTokenTable = $schema->getTable($this->databaseTablePrefix . 'oauth2_refresh_tokens');
        $oauth2RefreshTokenTable->modifyColumn('token', ['length' => 191]);
        $oauth2RefreshTokenTable->modifyColumn('scope', ['length' => 191]);

        $internalSettingTable = $schema->getTable($this->databaseTablePrefix . 'internal_setting');
        $internalSettingTable->modifyColumn('name', ['length' => 191]);
        $internalSettingTable->modifyColumn('section', ['length' => 191]);
        $internalSettingTable->modifyColumn('value', ['length' => 191]);
    }
}
