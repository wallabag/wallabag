<?php

namespace Wallabag\Consumer;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Wallabag\Entity\Entry;
use Wallabag\Event\EntrySavedEvent;
use Wallabag\Import\AbstractImport;
use Wallabag\Repository\UserRepository;

abstract class AbstractConsumer
{
    private readonly TokenStorageInterface $tokenStorage;
    private readonly HttpBrowser $browser;

    public function __construct(
        protected EntityManagerInterface $em,
        protected UserRepository $userRepository,
        protected AbstractImport $import,
        protected EventDispatcherInterface $eventDispatcher,
        protected ?LoggerInterface $logger = null,
        ?TokenStorageInterface $tokenStorage = null,
        ?HttpBrowser $browser = null,
    ) {
        $this->logger = $logger ?: new NullLogger();
        $this->tokenStorage = $tokenStorage ?? new TokenStorage();
        $this->browser = $browser ?? new HttpBrowser();
    }

    /**
     * Handle a message and save it.
     *
     * @param string $body Message from the queue (in json)
     *
     * @return bool
     */
    protected function handleMessage($body)
    {
        $storedEntry = json_decode($body, true);

        $user = $this->userRepository->find($storedEntry['userId']);

        // no user? Drop message
        if (null === $user) {
            $this->logger->warning('Unable to retrieve user', ['entry' => $storedEntry]);

            // return true to skip message
            return true;
        }

        // Website credentials are resolved through the security context.
        $previousToken = $this->tokenStorage->getToken();
        $this->browser->restart();
        $this->tokenStorage->setToken(new UsernamePasswordToken($user, 'main', $user->getRoles()));

        try {
            $this->import->setUser($user);

            if (false === $this->import->validateEntry($storedEntry)) {
                $this->logger->warning('Entry is invalid', ['entry' => $storedEntry]);

                // return true to skip message
                return true;
            }

            $entry = $this->import->parseEntry($storedEntry);

            if (null === $entry) {
                $this->logger->warning('Entry already exists', ['entry' => $storedEntry]);

                // return true to skip message
                return true;
            }

            try {
                $this->em->flush();

                // entry saved, dispatch event about it!
                $this->eventDispatcher->dispatch(new EntrySavedEvent($entry), EntrySavedEvent::NAME);

                $this->em->clear();
            } catch (\Exception $e) {
                $this->logger->warning('Unable to save entry', ['entry' => $storedEntry, 'exception' => $e]);

                return false;
            }

            $this->logger->info('Content with url imported! (' . $entry->getUrl() . ')');

            return true;
        } finally {
            // A worker must not reuse a website session for another user.
            $this->browser->restart();
            $this->tokenStorage->setToken($previousToken);
        }
    }
}
