<?php

namespace Wallabag\ImportBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FirefoxController extends BrowserController
{
    /**
     * {@inheritdoc}
     */
    protected function getImportService()
    {
        $service = $this->get('wallabag_import.firefox.import');

        if ($this->get('craue_config')->get('import_with_rabbitmq')) {
            $service->setProducer($this->get('old_sound_rabbit_mq.import_firefox_producer'));
        } elseif ($this->get('craue_config')->get('import_with_redis')) {
            $service->setProducer($this->get('wallabag_import.producer.redis.firefox'));
        }

        return $service;
    }

    /**
     * {@inheritdoc}
     */
    protected function getImportTemplate()
    {
        return 'WallabagImportBundle:Firefox:index.html.twig';
    }

    /**
     * @Route("/firefox", name="import_firefox")
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        return parent::indexAction($request);
    }
}
