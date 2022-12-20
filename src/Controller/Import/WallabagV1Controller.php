<?php

namespace App\Controller\Import;

use App\Import\WallabagV1Import;
use App\Redis\Producer as RedisProducer;
use Craue\ConfigBundle\Util\Config;
use OldSound\RabbitMqBundle\RabbitMq\Producer as RabbitMqProducer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * @Route("/import/wallabag-v1", name="import_wallabag_v1")
     */
    public function indexAction(Request $request, TranslatorInterface $translator)
    {
        return parent::indexAction($request, $translator);
    }

    /**
     * {@inheritdoc}
     */
    protected function getImportService()
    {
        if ($this->craueConfig->get('import_with_rabbitmq')) {
            $this->wallabagImport->setProducer($this->rabbitMqProducer);
        } elseif ($this->craueConfig->get('import_with_redis')) {
            $this->wallabagImport->setProducer($this->redisProducer);
        }

        return $this->wallabagImport;
    }

    /**
     * {@inheritdoc}
     */
    protected function getImportTemplate()
    {
        return 'Import/wallabagV1.html.twig';
    }
}
