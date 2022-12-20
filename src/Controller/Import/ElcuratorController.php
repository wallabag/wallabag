<?php

namespace App\Controller\Import;

use App\Import\ElcuratorImport;
use App\Redis\Producer as RedisProducer;
use Craue\ConfigBundle\Util\Config;
use OldSound\RabbitMqBundle\RabbitMq\Producer as RabbitMqProducer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ElcuratorController extends WallabagController
{
    private ElcuratorImport $elcuratorImport;
    private Config $craueConfig;
    private RabbitMqProducer $rabbitMqProducer;
    private RedisProducer $redisProducer;

    public function __construct(ElcuratorImport $elcuratorImport, Config $craueConfig, RabbitMqProducer $rabbitMqProducer, RedisProducer $redisProducer)
    {
        $this->elcuratorImport = $elcuratorImport;
        $this->craueConfig = $craueConfig;
        $this->rabbitMqProducer = $rabbitMqProducer;
        $this->redisProducer = $redisProducer;
    }

    /**
     * @Route("/import/elcurator", name="import_elcurator")
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
            $this->elcuratorImport->setProducer($this->rabbitMqProducer);
        } elseif ($this->craueConfig->get('import_with_redis')) {
            $this->elcuratorImport->setProducer($this->redisProducer);
        }

        return $this->elcuratorImport;
    }

    /**
     * {@inheritdoc}
     */
    protected function getImportTemplate()
    {
        return 'Import/elcurator.html.twig';
    }
}
