<?php

namespace Wallabag\ImportBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class WallabagV2Controller extends WallabagController
{
    /**
     * {@inheritdoc}
     */
    protected function getImportService()
    {
        $service = $this->get('wallabag_import.wallabag_v2.import');

        if ($this->get('craue_config')->get('rabbitmq')) {
            $service->setRabbitmqProducer($this->get('old_sound_rabbit_mq.import_wallabag_v2_producer'));
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

    /**
     * @Route("/wallabag-v2", name="import_wallabag_v2")
     */
    public function indexAction(Request $request)
    {
        return parent::indexAction($request);
    }
}
