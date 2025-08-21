<?php

namespace Wallabag\Controller\Import;

use Craue\ConfigBundle\Util\Config;
use OldSound\RabbitMqBundle\RabbitMq\Producer as RabbitMqProducer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wallabag\Import\WallabagV2Import;
use Wallabag\Redis\Producer as RedisProducer;

class WallabagV2Controller extends WallabagController
{
    public function __construct(
        private readonly WallabagV2Import $wallabagImport,
        private readonly Config $craueConfig,
        private readonly RabbitMqProducer $rabbitMqProducer,
        private readonly RedisProducer $redisProducer,
    ) {
    }

    #[Route(path: '/import/wallabag-v2', name: 'import_wallabag_v2', methods: ['GET', 'POST'])]
    #[IsGranted('IMPORT_ENTRIES')]
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
        return 'Import/WallabagV2/index.html.twig';
    }
}
