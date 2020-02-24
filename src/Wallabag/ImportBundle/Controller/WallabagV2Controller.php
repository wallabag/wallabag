<?php

namespace Wallabag\ImportBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WallabagV2Controller extends WallabagController
{
    /**
     * @Route("/wallabag-v2", name="import_wallabag_v2")
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
        $service = $this->get('wallabag_import.wallabag_v2.import');

        if ($this->get('craue_config')->get('import_with_rabbitmq')) {
            $service->setProducer($this->get('old_sound_rabbit_mq.import_wallabag_v2_producer'));
        } elseif ($this->get('craue_config')->get('import_with_redis')) {
            $service->setProducer($this->get('wallabag_import.producer.redis.wallabag_v2'));
        }

        return $service;
    }

    /**
     * {@inheritdoc}
     */
    protected function getImportTemplate()
    {
        return 'WallabagImportBundle:WallabagV2:index.html.twig';
    }
}
