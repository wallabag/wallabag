<?php

namespace Wallabag\Controller\Import;

use Craue\ConfigBundle\Util\Config;
use OldSound\RabbitMqBundle\RabbitMq\Producer as RabbitMqProducer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wallabag\Import\PocketCsvImport;
use Wallabag\Redis\Producer as RedisProducer;

class PocketCsvController extends HtmlController
{
    public function __construct(
        private readonly PocketCsvImport $pocketCsvImport,
        private readonly Config $craueConfig,
        private readonly RabbitMqProducer $rabbitMqProducer,
        private readonly RedisProducer $redisProducer,
    ) {
    }

    #[Route(path: '/import/pocket_csv', name: 'import_pocket_csv', methods: ['GET', 'POST'])]
    #[IsGranted('IMPORT_ENTRIES')]
    public function indexAction(Request $request, TranslatorInterface $translator)
    {
        return parent::indexAction($request, $translator);
    }

    protected function getImportService()
    {
        if ($this->craueConfig->get('import_with_rabbitmq')) {
            $this->pocketCsvImport->setProducer($this->rabbitMqProducer);
        } elseif ($this->craueConfig->get('import_with_redis')) {
            $this->pocketCsvImport->setProducer($this->redisProducer);
        }

        return $this->pocketCsvImport;
    }

    protected function getImportTemplate()
    {
        return 'Import/PocketCsv/index.html.twig';
    }
}
