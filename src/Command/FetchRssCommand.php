<?php

namespace Wallabag\Command;

use Doctrine\ORM\EntityManagerInterface;
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
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $previousToken = $this->tokens->getToken();
        $cookies = $this->browser->getCookieJar()->all();

        try {
            foreach ($this->feeds->iterateAll() as $feed) {
                $this->browser->restart();
                $user = $feed->getUser();
                $this->tokens->setToken(new UsernamePasswordToken($user, 'main', $user->getRoles()));

                try {
                    $io->text(\sprintf('Feed #%d: %d new items', $feed->getId(), $this->fetcher->fetch($feed)));
                } catch (\Exception $error) {
                    $io->error(\sprintf('Unable to refresh feed #%d (%s).', $feed->getId(), $error::class));
                    if (!$this->entityManager->isOpen()) {
                        return Command::FAILURE;
                    }
                }
            }
        } finally {
            $this->browser->restart();
            foreach ($cookies as $cookie) {
                $this->browser->getCookieJar()->set($cookie);
            }
            $this->tokens->setToken($previousToken);
        }

        return Command::SUCCESS;
    }
}
