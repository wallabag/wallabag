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
        return $this->get('wallabag_import.wallabag_v2.import');
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
