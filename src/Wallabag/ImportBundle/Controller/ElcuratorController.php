<?php

namespace Wallabag\ImportBundle\Controller;

use Craue\ConfigBundle\Util\Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\ImportBundle\Import\ElcuratorImport;

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
        $service = $this->get(ElcuratorImport::class);

        if ($this->get(Config::class)->get('import_with_rabbitmq')) {
            $service->setProducer($this->get('old_sound_rabbit_mq.import_elcurator_producer'));
        } elseif ($this->get(Config::class)->get('import_with_redis')) {
            $service->setProducer($this->get('wallabag_import.producer.redis.elcurator'));
        }

        return $service;
    }

    /**
     * {@inheritdoc}
     */
    protected function getImportTemplate()
    {
        return '@WallabagImport/Elcurator/index.html.twig';
    }
}
