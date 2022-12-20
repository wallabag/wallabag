<?php

namespace App\Controller\Import;

use App\Import\ChromeImport;
use App\Redis\Producer as RedisProducer;
use Craue\ConfigBundle\Util\Config;
use OldSound\RabbitMqBundle\RabbitMq\Producer as RabbitMqProducer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChromeController extends BrowserController
{
    private ChromeImport $chromeImport;
    private Config $craueConfig;
    private RabbitMqProducer $rabbitMqProducer;
    private RedisProducer $redisProducer;

    public function __construct(ChromeImport $chromeImport, Config $craueConfig, RabbitMqProducer $rabbitMqProducer, RedisProducer $redisProducer)
    {
        $this->chromeImport = $chromeImport;
        $this->craueConfig = $craueConfig;
        $this->rabbitMqProducer = $rabbitMqProducer;
        $this->redisProducer = $redisProducer;
    }

    /**
     * @Route("/import/chrome", name="import_chrome")
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
            $this->chromeImport->setProducer($this->rabbitMqProducer);
        } elseif ($this->craueConfig->get('import_with_redis')) {
            $this->chromeImport->setProducer($this->redisProducer);
        }

        return $this->chromeImport;
    }

    /**
     * {@inheritdoc}
     */
    protected function getImportTemplate()
    {
        return 'Import/chrome.html.twig';
    }
}
