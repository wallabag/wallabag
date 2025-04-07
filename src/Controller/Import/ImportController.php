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
                $nbRabbitMessages = $this->rabbitMQConsumerTotalProxy->getTotalMessage('pocket')
                    + $this->rabbitMQConsumerTotalProxy->getTotalMessage('readability')
                    + $this->rabbitMQConsumerTotalProxy->getTotalMessage('wallabag_v1')
                    + $this->rabbitMQConsumerTotalProxy->getTotalMessage('wallabag_v2')
                    + $this->rabbitMQConsumerTotalProxy->getTotalMessage('firefox')
                    + $this->rabbitMQConsumerTotalProxy->getTotalMessage('chrome')
                    + $this->rabbitMQConsumerTotalProxy->getTotalMessage('instapaper')
                    + $this->rabbitMQConsumerTotalProxy->getTotalMessage('pinboard')
                    + $this->rabbitMQConsumerTotalProxy->getTotalMessage('delicious')
                    + $this->rabbitMQConsumerTotalProxy->getTotalMessage('elcurator')
                    + $this->rabbitMQConsumerTotalProxy->getTotalMessage('shaarli')
                    + $this->rabbitMQConsumerTotalProxy->getTotalMessage('pocket_html')
                    + $this->rabbitMQConsumerTotalProxy->getTotalMessage('omnivore')
                ;
            } catch (\Exception) {
                $rabbitNotInstalled = true;
            }
        } elseif ($craueConfig->get('import_with_redis')) {
            try {
                $nbRedisMessages = $this->redisClient->llen('wallabag.import.pocket')
                    + $this->redisClient->llen('wallabag.import.readability')
                    + $this->redisClient->llen('wallabag.import.wallabag_v1')
                    + $this->redisClient->llen('wallabag.import.wallabag_v2')
                    + $this->redisClient->llen('wallabag.import.firefox')
                    + $this->redisClient->llen('wallabag.import.chrome')
                    + $this->redisClient->llen('wallabag.import.instapaper')
                    + $this->redisClient->llen('wallabag.import.pinboard')
                    + $this->redisClient->llen('wallabag.import.delicious')
                    + $this->redisClient->llen('wallabag.import.elcurator')
                    + $this->redisClient->llen('wallabag.import.shaarli')
                    + $this->redisClient->llen('wallabag.import.pocket_html')
                    + $this->redisClient->llen('wallabag.import.omnivore')
                ;
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
