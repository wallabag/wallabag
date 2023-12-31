<?php

namespace Wallabag\CoreBundle\Controller\Import;

use Craue\ConfigBundle\Util\Config;
use OldSound\RabbitMqBundle\RabbitMq\Producer as RabbitMqProducer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wallabag\CoreBundle\Import\ShaarliImport;
use Wallabag\ImportBundle\Redis\Producer as RedisProducer;

class ShaarliController extends HtmlController
{
    private ShaarliImport $shaarliImport;
    private Config $craueConfig;
    private RabbitMqProducer $rabbitMqProducer;
    private RedisProducer $redisProducer;

    public function __construct(ShaarliImport $shaarliImport, Config $craueConfig, RabbitMqProducer $rabbitMqProducer, RedisProducer $redisProducer)
    {
        $this->shaarliImport = $shaarliImport;
        $this->craueConfig = $craueConfig;
        $this->rabbitMqProducer = $rabbitMqProducer;
        $this->redisProducer = $redisProducer;
    }

    /**
     * @Route("/import/shaarli", name="import_shaarli")
     */
    public function indexAction(Request $request, TranslatorInterface $translator)
    {
        return parent::indexAction($request, $translator);
    }

    protected function getImportService()
    {
        if ($this->craueConfig->get('import_with_rabbitmq')) {
            $this->shaarliImport->setProducer($this->rabbitMqProducer);
        } elseif ($this->craueConfig->get('import_with_redis')) {
            $this->shaarliImport->setProducer($this->redisProducer);
        }

        return $this->shaarliImport;
    }

    protected function getImportTemplate()
    {
        return '@WallabagImport/Shaarli/index.html.twig';
    }
}
