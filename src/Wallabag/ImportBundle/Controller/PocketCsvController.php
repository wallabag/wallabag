<?php

namespace Wallabag\ImportBundle\Controller;

use Craue\ConfigBundle\Util\Config;
use OldSound\RabbitMqBundle\RabbitMq\Producer as RabbitMqProducer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wallabag\ImportBundle\Import\PocketCsvImport;
use Wallabag\ImportBundle\Redis\Producer as RedisProducer;

class PocketCsvController extends HtmlController
{
    private PocketCsvImport $pocketCsvImport;
    private Config $craueConfig;
    private RabbitMqProducer $rabbitMqProducer;
    private RedisProducer $redisProducer;

    public function __construct(PocketCsvImport $pocketCsvImport, Config $craueConfig, RabbitMqProducer $rabbitMqProducer, RedisProducer $redisProducer)
    {
        $this->pocketCsvImport = $pocketCsvImport;
        $this->craueConfig = $craueConfig;
        $this->rabbitMqProducer = $rabbitMqProducer;
        $this->redisProducer = $redisProducer;
    }

    /**
     * @Route("/pocket_csv", name="import_pocket_csv")
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
            $this->pocketCsvImport->setProducer($this->rabbitMqProducer);
        } elseif ($this->craueConfig->get('import_with_redis')) {
            $this->pocketCsvImport->setProducer($this->redisProducer);
        }

        return $this->pocketCsvImport;
    }

    /**
     * {@inheritdoc}
     */
    protected function getImportTemplate()
    {
        return '@WallabagImport/PocketCsv/index.html.twig';
    }
}
