<?php

namespace Wallabag\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
* Archive controller.
*/
class ArchiveController
{

    public function indexAction(Request $request, Application $app)
    {
        $entries = $app['entry_api']->getEntries('read');

        return $app['twig']->render('archive.twig', array('entries' => $entries));
    }

    public function readAction(Request $request, Application $app, $id)
    {
        $entry = $app['entry_api']->markAsRead($id);
        $referer = $request->headers->get('referer');

        return $app->redirect($referer);
    }

    public function unreadAction(Request $request, Application $app, $id)
    {
        $entry = $app['entry_api']->markAsUnread($id);
        $referer = $request->headers->get('referer');

        return $app->redirect($referer);
    }
}
