<?php

namespace Wallabag\Command;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Wallabag\Helper\FeedFetcher;
use Wallabag\Repository\FeedUrlRepository;

#[AsCommand(name: 'wallabag:fetch-rss', description: 'Refresh feed subscriptions for all users')]
class FetchRssCommand extends Command
{
    public function __construct(
        private readonly FeedUrlRepository $feeds,
        private readonly FeedFetcher $fetcher,
        private readonly EntityManagerInterface $entityManager,
        private readonly TokenStorageInterface $tokens,
        private readonly HttpBrowser $browser,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $previousToken = $this->tokens->getToken();
        $cookies = $this->browser->getCookieJar()->all();
        $succeeded = 0;
        $failed = 0;
        $newItems = 0;
        $stoppedEarly = false;

        try {
            foreach ($this->feeds->iterateAll() as $feed) {
                try {
                    $this->browser->restart();
                    $user = $feed->getUser();
                    $this->tokens->setToken(new UsernamePasswordToken($user, 'main', $user->getRoles()));

                    $collected = $this->fetcher->fetch($feed);
                    ++$succeeded;
                    $newItems += $collected;
                    $io->text(\sprintf('Feed #%d: %d new items', $feed->getId(), $collected));
                } catch (\Throwable $error) {
                    ++$failed;
                    $this->logger->error('Unable to refresh feed subscription.', [
                        'feed_id' => $feed->getId(),
                        'exception_type' => $error::class,
                    ]);
                    $io->error(\sprintf('Unable to refresh feed #%d (%s).', $feed->getId(), $error::class));
                    if (!$this->entityManager->isOpen()) {
                        $stoppedEarly = true;
                        break;
                    }
                } finally {
                    $this->entityManager->clear();
                }
            }
        } finally {
            $this->browser->restart();
            foreach ($cookies as $cookie) {
                $this->browser->getCookieJar()->set($cookie);
            }
            $this->tokens->setToken($previousToken);
        }

        if ($stoppedEarly) {
            $io->warning('Stopped processing subscriptions because the entity manager closed.');
        }
        $io->text(\sprintf('Refresh complete. Successful subscriptions: %d. Failed subscriptions: %d. New items: %d.', $succeeded, $failed, $newItems));

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
