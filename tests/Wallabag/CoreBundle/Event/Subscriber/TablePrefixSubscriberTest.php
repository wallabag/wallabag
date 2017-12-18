<?php

namespace Tests\Wallabag\CoreBundle\Event\Subscriber;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Wallabag\CoreBundle\Event\Subscriber\TablePrefixSubscriber;

class TablePrefixSubscriberTest extends TestCase
{
    public function dataForPrefix()
    {
        return [
            ['wallabag_', 'Wallabag\UserBundle\Entity\User', '`user`', 'user', 'wallabag_user', '"wallabag_user"', new \Doctrine\DBAL\Platforms\PostgreSqlPlatform()],
            ['wallabag_', 'Wallabag\UserBundle\Entity\User', '`user`', 'user', 'wallabag_user', '`wallabag_user`', new \Doctrine\DBAL\Platforms\MySqlPlatform()],
            ['wallabag_', 'Wallabag\UserBundle\Entity\User', '`user`', 'user', 'wallabag_user', '"wallabag_user"', new \Doctrine\DBAL\Platforms\SqlitePlatform()],

            ['wallabag_', 'Wallabag\UserBundle\Entity\User', 'user', 'user', 'wallabag_user', 'wallabag_user', new \Doctrine\DBAL\Platforms\PostgreSqlPlatform()],
            ['wallabag_', 'Wallabag\UserBundle\Entity\User', 'user', 'user', 'wallabag_user', 'wallabag_user', new \Doctrine\DBAL\Platforms\MySqlPlatform()],
            ['wallabag_', 'Wallabag\UserBundle\Entity\User', 'user', 'user', 'wallabag_user', 'wallabag_user', new \Doctrine\DBAL\Platforms\SqlitePlatform()],

            ['', 'Wallabag\UserBundle\Entity\User', '`user`', 'user', 'user', '"user"', new \Doctrine\DBAL\Platforms\PostgreSqlPlatform()],
            ['', 'Wallabag\UserBundle\Entity\User', '`user`', 'user', 'user', '`user`', new \Doctrine\DBAL\Platforms\MySqlPlatform()],
            ['', 'Wallabag\UserBundle\Entity\User', '`user`', 'user', 'user', '"user"', new \Doctrine\DBAL\Platforms\SqlitePlatform()],

            ['', 'Wallabag\UserBundle\Entity\User', 'user', 'user', 'user', 'user', new \Doctrine\DBAL\Platforms\PostgreSqlPlatform()],
            ['', 'Wallabag\UserBundle\Entity\User', 'user', 'user', 'user', 'user', new \Doctrine\DBAL\Platforms\MySqlPlatform()],
            ['', 'Wallabag\UserBundle\Entity\User', 'user', 'user', 'user', 'user', new \Doctrine\DBAL\Platforms\SqlitePlatform()],
        ];
    }

    /**
     * @dataProvider dataForPrefix
     */
    public function testPrefix($prefix, $entityName, $tableName, $tableNameExpected, $finalTableName, $finalTableNameQuoted, $platform)
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $subscriber = new TablePrefixSubscriber($prefix);

        $metaClass = new ClassMetadata($entityName);
        $metaClass->setPrimaryTable(['name' => $tableName]);

        $metaDataEvent = new LoadClassMetadataEventArgs($metaClass, $em);

        $this->assertSame($tableNameExpected, $metaDataEvent->getClassMetadata()->getTableName());

        $subscriber->loadClassMetadata($metaDataEvent);

        $this->assertSame($finalTableName, $metaDataEvent->getClassMetadata()->getTableName());
        $this->assertSame($finalTableNameQuoted, $metaDataEvent->getClassMetadata()->getQuotedTableName($platform));
    }

    /**
     * @dataProvider dataForPrefix
     */
    public function testSubscribedEvents($prefix, $entityName, $tableName, $tableNameExpected, $finalTableName, $finalTableNameQuoted, $platform)
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
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
        $this->assertSame($finalTableNameQuoted, $metaDataEvent->getClassMetadata()->getQuotedTableName($platform));
    }

    public function testPrefixManyToMany()
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $subscriber = new TablePrefixSubscriber('yo_');

        $metaClass = new ClassMetadata('Wallabag\UserBundle\Entity\Entry');
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
        $this->assertSame('yo_entry', $metaDataEvent->getClassMetadata()->getQuotedTableName(new \Doctrine\DBAL\Platforms\MySqlPlatform()));
    }
}
