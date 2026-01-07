<?php

namespace Wallabag\Command\Import;

use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\Logging\Middleware as LoggingMiddleware;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Helper\ContentProxy;
use Wallabag\Helper\TagsAssigner;
use Wallabag\Repository\UserRepository;

class UrlCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly UserRepository $userRepository,
        private readonly ContentProxy $contentProxy,
        private readonly TagsAssigner $tagsAssigner,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('wallabag:import:url')
            ->setDescription('Import a single URL')
            ->addArgument('username', InputArgument::REQUIRED, 'User to add the URL to (value: username or id)')
            ->addArgument('url', InputArgument::REQUIRED, 'URL to import')
            ->addArgument('tags', InputArgument::OPTIONAL, 'Comma-separated list of tags to add')
            ->addOption('markAsRead', null, InputOption::VALUE_OPTIONAL, 'Mark entry as read', false)
            ->addOption('useUserId', null, InputOption::VALUE_NONE, 'Use user id instead of username to find account')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Turning off doctrine default logs queries for saving memory
        $middlewares = $this->entityManager->getConnection()->getConfiguration()->getMiddlewares();
        $middlewaresWithoutLogging = array_filter($middlewares, fn (Middleware $middleware) => !$middleware instanceof LoggingMiddleware);
        $this->entityManager->getConnection()->getConfiguration()->setMiddlewares($middlewaresWithoutLogging);

        if ($input->getOption('useUserId')) {
            $entityUser = $this->userRepository->findOneById($input->getArgument('username'));
        } else {
            $entityUser = $this->userRepository->findOneByUsername($input->getArgument('username'));
        }

        if (!\is_object($entityUser)) {
            throw new Exception(\sprintf('User "%s" not found', $input->getArgument('username')));
        }

        // Authenticate user for paywalled websites
        $token = new UsernamePasswordToken(
            $entityUser,
            'main',
            $entityUser->getRoles()
        );

        $this->tokenStorage->setToken($token);
        $user = $this->tokenStorage->getToken()->getUser();
        \assert($user instanceof User);

        $url = $input->getArgument('url');

        $existingEntry = $this->entityManager
            ->getRepository(Entry::class)
            ->findByUrlAndUserId($url, $user->getId());

        if (false !== $existingEntry) {
            $output->writeln(\sprintf('The URL %s is already in user’s entries.', $url));

            return 1;
        }

        $entry = new Entry($user);

        try {
            $this->contentProxy->updateEntry($entry, $url);
        } catch (\Exception $e) {
            $output->writeln(\sprintf('Error trying to import the URL %s: %s.', $url, $e->getMessage()));

            return 1;
        }

        if ($input->getOption('markAsRead')) {
            $entry->updateArchived(true);
        }

        $this->entityManager->persist($entry);

        $tags = explode(',', $input->getArgument('tags'));
        if (\count($tags) > 1) {
            $this->tagsAssigner->assignTagsToEntry(
                $entry,
                $tags,
                $this->entityManager->getUnitOfWork()->getScheduledEntityInsertions()
            );
        }

        $this->entityManager->flush();

        $output->writeln(\sprintf('URL %s successfully imported.', $url));

        return 0;
    }
}
