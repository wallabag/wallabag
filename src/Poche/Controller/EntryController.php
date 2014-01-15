<?php

namespace Poche\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
* Entry controller.
*/
class EntryController
{

    public function indexAction(Request $request, Application $app)
    {
        $entries = $app['entry_api']->getEntries('unread');

        return $app['twig']->render('index.twig', array('entries' => $entries));
    }
}
