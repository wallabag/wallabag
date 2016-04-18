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
    }

    public function postUp(Schema $schema)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('WallabagCoreBundle:Entry');
        $entries = $repository->findAll();

        /** @var Entry $entry */
        foreach ($entries as $entry) {
            $this->addSql('UPDATE `wallabag_entry` SET `uuid` = "'.uniqid('', true).'" WHERE id = '.$entry->getId());
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'sqlite', 'This down migration can\'t be executed on SQLite databases, because SQLite don\'t support DROP COLUMN.');
        $this->addSql('ALTER TABLE `wallabag_entry` DROP `uuid`');
    }
}
