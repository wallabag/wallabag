<?php

// Read-only comparison of the migrated isolated SQLite schema with ORM metadata.
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/AppKernel.php';
$kernel = new AppKernel('test', true);
$kernel->boot();
$em = $kernel->getContainer()->get(Doctrine\ORM\EntityManagerInterface::class);
$metadata = $em->getMetadataFactory()->getAllMetadata();
$tool = new Doctrine\ORM\Tools\SchemaTool($em);
$sql = $tool->getUpdateSchemaSql($metadata);
file_put_contents(__DIR__ . '/../.validation/schema-drift.sql', implode(";\n", $sql));
foreach ($sql as $statement) {
    if (preg_match('/\brss_feed\b|\brss_seen_item\b|\bwallabag_rss_feed\b|\bwallabag_rss_seen_item\b/i', $statement)) {
        throw new RuntimeException('RSS migration drift: ' . $statement);
    }
}
$desired = $tool->getSchemaFromMetadata($metadata);
$actual = $em->getConnection()->getSchemaManager()->createSchema();
$table = $em->getClassMetadata(Wallabag\CoreBundle\Entity\Config::class)->getTableName();
$comparator = new Doctrine\DBAL\Schema\Comparator();
if ($comparator->diffColumn($actual->getTable($table)->getColumn('x_article_reader_enabled'), $desired->getTable($table)->getColumn('x_article_reader_enabled'))) {
    throw new RuntimeException('X reader preference column differs from ORM mapping.');
}
$entryTable = $em->getClassMetadata(Wallabag\CoreBundle\Entity\Entry::class)->getTableName();
if ($comparator->diffColumn($actual->getTable($entryTable)->getColumn('x_capture_type'), $desired->getTable($entryTable)->getColumn('x_capture_type'))) {
    throw new RuntimeException('X capture provenance column differs from ORM mapping.');
}
$legacyMetadata = [];
foreach ($metadata as $class) {
    if (in_array($class->getName(), [Wallabag\CoreBundle\Entity\RssFeed::class, Wallabag\CoreBundle\Entity\RssSeenItem::class], true)) {
        continue;
    }
    $copy = clone $class;
    if (Wallabag\CoreBundle\Entity\Config::class === $copy->getName()) {
        unset($copy->fieldMappings['xArticleReaderEnabled']);
    }
    $legacyMetadata[] = $copy;
}
$legacyActual = clone $actual;
foreach ([Wallabag\CoreBundle\Entity\RssFeed::class, Wallabag\CoreBundle\Entity\RssSeenItem::class] as $class) {
    $legacyActual->dropTable($em->getClassMetadata($class)->getTableName());
}
$legacyActual->getTable($table)->dropColumn('x_article_reader_enabled');
$legacyDesired = $tool->getSchemaFromMetadata($legacyMetadata);
$legacySql = $comparator->compare($legacyActual, $legacyDesired)->toSaveSql($em->getConnection()->getDatabasePlatform());
echo json_encode(['rss_schema_matches' => true, 'x_preference_column_matches' => true, 'other_schema_statements' => count($sql), 'legacy_only_schema_statements' => count($legacySql)]) . "\n";
$kernel->shutdown();
