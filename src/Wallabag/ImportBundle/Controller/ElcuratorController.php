<?php

namespace Wallabag\ImportBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ElcuratorController extends WallabagController
{
    /**
     * @Route("/elcurator", name="import_elcurator")
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
        $service = $this->get('wallabag_import.elcurator.import');

        if ($this->get('craue_config')->get('import_with_rabbitmq')) {
            $service->setProducer($this->get('old_sound_rabbit_mq.import_elcurator_producer'));
        } elseif ($this->get('craue_config')->get('import_with_redis')) {
            $service->setProducer($this->get('wallabag_import.producer.redis.elcurator'));
        }

        return $service;
    }

    /**
     * {@inheritdoc}
     */
    protected function getImportTemplate()
    {
        return 'WallabagImportBundle:Elcurator:index.html.twig';
    }
}
