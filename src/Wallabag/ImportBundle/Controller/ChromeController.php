<?php

namespace Wallabag\ImportBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\ImportBundle\Import\ChromeImport;

class ChromeController extends BrowserController
{
    /**
     * @Route("/chrome", name="import_chrome")
     */
    public function indexAction(Request $request)
    {
        return parent::indexAction($request);
    }

    /**
     * {@inheritdoc}
     */
    protected function getImportService()
    {
        $service = $this->get(ChromeImport::class);

        if ($this->get('craue_config')->get('import_with_rabbitmq')) {
            $service->setProducer($this->get('old_sound_rabbit_mq.import_chrome_producer'));
        } elseif ($this->get('craue_config')->get('import_with_redis')) {
            $service->setProducer($this->get('wallabag_import.producer.redis.chrome'));
        }

        return $service;
    }

    /**
     * {@inheritdoc}
     */
    protected function getImportTemplate()
    {
        return '@WallabagImport/Chrome/index.html.twig';
    }
}
