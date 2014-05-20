<?php

namespace Wallabag\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
* Bookmark controller.
*/
class BookmarkController
{

    public function indexAction(Request $request, Application $app)
    {
        $entries = $app['entry_api']->getBookmarks();

        return $app['twig']->render('bookmarks.twig', array('entries' => $entries));
    }

    public function addAction(Request $request, Application $app, $id)
    {
        $entry = $app['entry_api']->star($id);
        $referer = $request->headers->get('referer');

        return $app->redirect($referer);
    }

    public function removeAction(Request $request, Application $app, $id)
    {
        $entry = $app['entry_api']->unstar($id);
        $referer = $request->headers->get('referer');

        return $app->redirect($referer);
    }
}
