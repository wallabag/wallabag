<?php

namespace Wallabag\CoreBundle\Event\Subscriber;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Wallabag\CoreBundle\Entity\Entry;

/**
 * SQLite doesn't care about cascading remove, so we need to manually remove associated stuf for an Entry.
 * Foreign Key Support can be enabled by running `PRAGMA foreign_keys = ON;` at runtime (AT RUNTIME !).
 * But it needs a compilation flag that not all SQLite instance has ...
 *
 * @see https://www.sqlite.org/foreignkeys.html#fk_enable
 */
class SQLiteCascadeDeleteSubscriber implements EventSubscriber
{
    private $doctrine;

    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'preRemove',
        ];
    }

    /**
     * We removed everything related to the upcoming removed entry because SQLite can't handle it on it own.
     * We do it in the preRemove, because we can't retrieve tags in the postRemove (because the entry id is gone).
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$this->doctrine->getConnection()->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform
            || !$entity instanceof Entry) {
            return;
        }

        $em = $this->doctrine->getManager();

        if (null !== $entity->getTags()) {
            foreach ($entity->getTags() as $tag) {
                $entity->removeTag($tag);
            }
        }

        if (null !== $entity->getAnnotations()) {
            foreach ($entity->getAnnotations() as $annotation) {
                $em->remove($annotation);
            }
        }

        $em->flush();
    }
}
