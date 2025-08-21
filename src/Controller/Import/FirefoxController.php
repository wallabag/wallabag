<?php

namespace Wallabag\Controller\Import;

use Craue\ConfigBundle\Util\Config;
use OldSound\RabbitMqBundle\RabbitMq\Producer as RabbitMqProducer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wallabag\Import\FirefoxImport;
use Wallabag\Redis\Producer as RedisProducer;

class FirefoxController extends BrowserController
{
    public function __construct(
        private readonly FirefoxImport $firefoxImport,
        private readonly Config $craueConfig,
        private readonly RabbitMqProducer $rabbitMqProducer,
        private readonly RedisProducer $redisProducer,
    ) {
    }

    #[Route(path: '/import/firefox', name: 'import_firefox', methods: ['GET', 'POST'])]
    #[IsGranted('IMPORT_ENTRIES')]
    public function indexAction(Request $request, TranslatorInterface $translator)
    {
        return parent::indexAction($request, $translator);
    }

    protected function getImportService()
    {
        if ($this->craueConfig->get('import_with_rabbitmq')) {
            $this->firefoxImport->setProducer($this->rabbitMqProducer);
        } elseif ($this->craueConfig->get('import_with_redis')) {
            $this->firefoxImport->setProducer($this->redisProducer);
        }

        return $this->firefoxImport;
    }

    protected function getImportTemplate()
    {
        return 'Import/Firefox/index.html.twig';
    }
}
