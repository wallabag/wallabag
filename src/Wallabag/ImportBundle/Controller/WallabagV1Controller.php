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
        return $this->get('wallabag_import.wallabag_v1.import');
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
