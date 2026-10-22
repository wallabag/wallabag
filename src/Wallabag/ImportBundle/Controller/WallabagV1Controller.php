<?php

namespace Wallabag\ImportBundle\Controller;

use Craue\ConfigBundle\Util\Config;
use OldSound\RabbitMqBundle\RabbitMq\Producer as RabbitMqProducer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wallabag\ImportBundle\Import\WallabagV1Import;
use Wallabag\ImportBundle\Redis\Producer as RedisProducer;

class WallabagV1Controller extends WallabagController
{
    private WallabagV1Import $wallabagImport;
    private Config $craueConfig;
    private RabbitMqProducer $rabbitMqProducer;
    private RedisProducer $redisProducer;

    public function __construct(WallabagV1Import $wallabagImport, Config $craueConfig, RabbitMqProducer $rabbitMqProducer, RedisProducer $redisProducer)
    {
        $this->wallabagImport = $wallabagImport;
        $this->craueConfig = $craueConfig;
        $this->rabbitMqProducer = $rabbitMqProducer;
        $this->redisProducer = $redisProducer;
    }

    /**
     * @Route("/wallabag-v1", name="import_wallabag_v1")
     */
    public function indexAction(Request $request, TranslatorInterface $translator)
    {
        return parent::indexAction($request, $translator);
    }

    protected function getImportService()
    {
        if ($this->craueConfig->get('import_with_rabbitmq')) {
            $this->wallabagImport->setProducer($this->rabbitMqProducer);
        } elseif ($this->craueConfig->get('import_with_redis')) {
            $this->wallabagImport->setProducer($this->redisProducer);
        }

        return $this->wallabagImport;
    }

    protected function getImportTemplate()
    {
        return '@WallabagImport/WallabagV1/index.html.twig';
    }
}
