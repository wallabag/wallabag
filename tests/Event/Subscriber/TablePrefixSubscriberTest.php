<?php

namespace Tests\Wallabag\Event\Subscriber;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\DefaultQuoteStrategy;
use PHPUnit\Framework\TestCase;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Event\Subscriber\TablePrefixSubscriber;

class TablePrefixSubscriberTest extends TestCase
{
    public function dataForPrefix()
    {
        return [
            ['wallabag_', User::class, '`user`', 'user', 'wallabag_user', '"wallabag_user"', new PostgreSQLPlatform()],
            ['wallabag_', User::class, '`user`', 'user', 'wallabag_user', '`wallabag_user`', new MySQLPlatform()],
            ['wallabag_', User::class, '`user`', 'user', 'wallabag_user', '"wallabag_user"', new SqlitePlatform()],

            ['wallabag_', User::class, 'user', 'user', 'wallabag_user', 'wallabag_user', new PostgreSQLPlatform()],
            ['wallabag_', User::class, 'user', 'user', 'wallabag_user', 'wallabag_user', new MySQLPlatform()],
            ['wallabag_', User::class, 'user', 'user', 'wallabag_user', 'wallabag_user', new SqlitePlatform()],

            ['', User::class, '`user`', 'user', 'user', '"user"', new PostgreSQLPlatform()],
            ['', User::class, '`user`', 'user', 'user', '`user`', new MySQLPlatform()],
            ['', User::class, '`user`', 'user', 'user', '"user"', new SqlitePlatform()],

            ['', User::class, 'user', 'user', 'user', 'user', new PostgreSQLPlatform()],
            ['', User::class, 'user', 'user', 'user', 'user', new MySQLPlatform()],
            ['', User::class, 'user', 'user', 'user', 'user', new SqlitePlatform()],
        ];
    }

    /**
     * @dataProvider dataForPrefix
     */
    public function testPrefix($prefix, $entityName, $tableName, $tableNameExpected, $finalTableName, $finalTableNameQuoted, $platform)
    {
        $em = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $subscriber = new TablePrefixSubscriber($prefix);

        $metaClass = new ClassMetadata($entityName);
        $metaClass->setPrimaryTable(['name' => $tableName]);

        $metaDataEvent = new LoadClassMetadataEventArgs($metaClass, $em);

        $this->assertSame($tableNameExpected, $metaDataEvent->getClassMetadata()->getTableName());

        $subscriber->loadClassMetadata($metaDataEvent);

        $this->assertSame($finalTableName, $metaDataEvent->getClassMetadata()->getTableName());
        $this->assertSame($finalTableNameQuoted, (new DefaultQuoteStrategy())->getTableName($metaClass, $platform));
    }

    /**
     * @dataProvider dataForPrefix
     */
    public function testSubscribedEvents($prefix, $entityName, $tableName, $tableNameExpected, $finalTableName, $finalTableNameQuoted, $platform)
    {
        $em = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $metaClass = new ClassMetadata($entityName);
        $metaClass->setPrimaryTable(['name' => $tableName]);

        $metaDataEvent = new LoadClassMetadataEventArgs($metaClass, $em);

        $subscriber = new TablePrefixSubscriber($prefix);

        $evm = new EventManager();
        $evm->addEventSubscriber($subscriber);

        $evm->dispatchEvent('loadClassMetadata', $metaDataEvent);

        $this->assertSame($finalTableName, $metaDataEvent->getClassMetadata()->getTableName());
        $this->assertSame($finalTableNameQuoted, (new DefaultQuoteStrategy())->getTableName($metaClass, $platform));
    }

    public function testPrefixManyToMany()
    {
        $em = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $subscriber = new TablePrefixSubscriber('yo_');

        $metaClass = new ClassMetadata(Entry::class);
        $metaClass->setPrimaryTable(['name' => 'entry']);
        $metaClass->mapManyToMany([
            'fieldName' => 'tags',
            'joinTable' => ['name' => null, 'schema' => null],
            'targetEntity' => 'Tag',
            'mappedBy' => null,
            'inversedBy' => 'entries',
            'cascade' => ['persist'],
            'indexBy' => null,
            'orphanRemoval' => false,
            'fetch' => 2,
        ]);

        $metaDataEvent = new LoadClassMetadataEventArgs($metaClass, $em);

        $this->assertSame('entry', $metaDataEvent->getClassMetadata()->getTableName());

        $subscriber->loadClassMetadata($metaDataEvent);

        $this->assertSame('yo_entry', $metaDataEvent->getClassMetadata()->getTableName());
        $this->assertSame('yo_entry_tag', $metaDataEvent->getClassMetadata()->associationMappings['tags']['joinTable']['name']);
        $this->assertSame('yo_entry', (new DefaultQuoteStrategy())->getTableName($metaClass, new MySQLPlatform()));
    }
}
