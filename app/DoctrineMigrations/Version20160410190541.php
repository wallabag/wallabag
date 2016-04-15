<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Wallabag\CoreBundle\Entity\Entry;

class Version20160410190541 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE `wallabag_entry` ADD `uuid` LONGTEXT DEFAULT NULL');

        $em = $this->container->get('doctrine.orm.entity_manager');
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('e.uuid')
            ->andWhere('e.uuid IS NULL');
        $entries = $queryBuilder->execute();

        /** @var Entry $entry */
        foreach ($entries as $entry) {
            $entry->generateUuid();
            $em->persist($entry);
            $em->clear();
        }
        $em->flush();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE `wallabag_entry` DROP `uuid`');
    }
}
