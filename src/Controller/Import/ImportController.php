<?php

namespace Wallabag\Controller\Import;

use Craue\ConfigBundle\Util\Config;
use Predis\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Wallabag\Consumer\RabbitMQConsumerTotalProxy;
use Wallabag\Controller\AbstractController;
use Wallabag\Import\ImportChain;

class ImportController extends AbstractController
{
    public function __construct(
        private readonly RabbitMQConsumerTotalProxy $rabbitMQConsumerTotalProxy,
        private readonly Client $redisClient,
        private readonly array $importQueues,
    ) {
    }

    #[Route(path: '/import/', name: 'import', methods: ['GET'])]
    #[IsGranted('IMPORT_ENTRIES')]
    public function importAction(ImportChain $importChain)
    {
        return $this->render('Import/index.html.twig', [
            'imports' => $importChain->getAll(),
        ]);
    }

    /**
     * Display how many messages are queue (both in Redis and RabbitMQ).
     * Only for admins.
     */
    public function checkQueueAction(AuthorizationCheckerInterface $authorizationChecker, Config $craueConfig)
    {
        $nbRedisMessages = null;
        $nbRabbitMessages = null;
        $redisNotInstalled = false;
        $rabbitNotInstalled = false;

        if (!$authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->render('Import/check_queue.html.twig');
        }

        if ($craueConfig->get('import_with_rabbitmq')) {
            // in case rabbit is activated but not installed
            try {
                $total = 0;

                foreach ($this->importQueues as $queue) {
                    $total += $this->rabbitMQConsumerTotalProxy->getTotalMessage($queue);
                }

                $nbRabbitMessages = $total;
            } catch (\Exception) {
                $rabbitNotInstalled = true;
            }
        } elseif ($craueConfig->get('import_with_redis')) {
            try {
                $total = 0;

                foreach ($this->importQueues as $queue) {
                    $total += $this->redisClient->llen('wallabag.import.' . $queue);
                }

                $nbRedisMessages = $total;
            } catch (\Exception) {
                $redisNotInstalled = true;
            }
        }

        return $this->render('Import/check_queue.html.twig', [
            'nbRedisMessages' => $nbRedisMessages,
            'nbRabbitMessages' => $nbRabbitMessages,
            'redisNotInstalled' => $redisNotInstalled,
            'rabbitNotInstalled' => $rabbitNotInstalled,
        ]);
    }
}
