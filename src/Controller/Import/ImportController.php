<?php

namespace Wallabag\Controller\Import;

use Craue\ConfigBundle\Util\Config;
use Predis\Client;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Wallabag\Consumer\RabbitMQConsumerTotalProxy;
use Wallabag\Controller\AbstractController;
use Wallabag\Import\ImportChain;

class ImportController extends AbstractController
{
    private RabbitMQConsumerTotalProxy $rabbitMQConsumerTotalProxy;

    public function __construct(RabbitMQConsumerTotalProxy $rabbitMQConsumerTotalProxy)
    {
        $this->rabbitMQConsumerTotalProxy = $rabbitMQConsumerTotalProxy;
    }

    /**
     * @Route("/import/", name="import")
     */
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
            } catch (\Exception $e) {
                $rabbitNotInstalled = true;
            }
        } elseif ($craueConfig->get('import_with_redis')) {
            $redis = $this->get(Client::class);

            try {
                $nbRedisMessages = $redis->llen('wallabag.import.pocket')
                    + $redis->llen('wallabag.import.readability')
                    + $redis->llen('wallabag.import.wallabag_v1')
                    + $redis->llen('wallabag.import.wallabag_v2')
                    + $redis->llen('wallabag.import.firefox')
                    + $redis->llen('wallabag.import.chrome')
                    + $redis->llen('wallabag.import.instapaper')
                    + $redis->llen('wallabag.import.pinboard')
                    + $redis->llen('wallabag.import.delicious')
                    + $redis->llen('wallabag.import.elcurator')
                    + $redis->llen('wallabag.import.shaarli')
                    + $redis->llen('wallabag.import.pocket_html')
                    + $redis->llen('wallabag.import.omnivore')
                ;
            } catch (\Exception $e) {
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
