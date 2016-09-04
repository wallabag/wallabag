<?php

namespace Wallabag\ImportBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class WallabagV1Controller extends WallabagController
{
    /**
     * {@inheritdoc}
     */
    protected function getImportService()
    {
        $service = $this->get('wallabag_import.wallabag_v1.import');

        if ($this->get('craue_config')->get('rabbitmq')) {
            $service->setRabbitmqProducer($this->get('old_sound_rabbit_mq.import_wallabag_v1_producer'));
        }

        return $service;
    }

    /**
     * {@inheritdoc}
     */
    protected function getImportTemplate()
    {
        return 'WallabagImportBundle:WallabagV1:index.html.twig';
    }

    /**
     * @Route("/wallabag-v1", name="import_wallabag_v1")
     */
    public function indexAction(Request $request)
    {
        return parent::indexAction($request);
    }
}
