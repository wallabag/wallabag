<?php

namespace Wallabag\ImportBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WallabagV2Controller extends WallabagController
{
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

    /**
     * @Route("/wallabag-v2", name="import_wallabag_v2")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function indexAction(Request $request)
    {
        return parent::indexAction($request);
    }
}
