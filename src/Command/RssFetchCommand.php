<?php

namespace Wallabag\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wallabag\Entity\RssFeed;
use Wallabag\Rss\FeedImporter;

class RssFetchCommand extends Command
{
    private EntityManagerInterface $em;
    private FeedImporter $importer;
    private string $projectDir;
    public function __construct(EntityManagerInterface $em, FeedImporter $importer, string $projectDir) { $this->em = $em; $this->importer = $importer; $this->projectDir = $projectDir; parent::__construct(); }
    protected function configure() { $this->setName('wallabag:rss:fetch')->setDescription('Import due RSS subscriptions (run every minute; feeds poll every 30 minutes).'); }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lock = fopen($this->projectDir . '/var/rss-fetch.lock', 'c');
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
            $output->writeln('Another RSS importer is active, or the lock is unavailable.');
            return 1;
        }
        try {
            $feeds = $this->em->createQueryBuilder()->select('f')->from(RssFeed::class, 'f')->join('f.user', 'u')->where('f.enabled = true')->andWhere('u.enabled = true')->andWhere('f.nextFetchAt IS NULL OR f.nextFetchAt <= :now')->setParameter('now', new \DateTime())->orderBy('f.nextFetchAt', 'ASC')->setMaxResults(10)->getQuery()->getResult();
            $failed = false;
            foreach ($feeds as $feed) {
                if ($this->importer->run($feed)) {
                    $output->writeln('Feed ' . $feed->id . ': ' . ($feed->lastError ? 'failed/partial; see feed status' : 'ok'));
                    $failed = $failed || (bool) $feed->lastError;
                }
            }
            return $failed ? 1 : 0;
        } finally { flock($lock, LOCK_UN); fclose($lock); }
    }
}
