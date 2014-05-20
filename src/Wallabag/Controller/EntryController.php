<?php

namespace Wallabag\Controller;

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

        return $app['twig']->render('index.twig', array(
            'error' => $app['security.last_error']($request),
            'entries' => $entries,
        ));
    }

    public function showAction(Request $request, Application $app, $id)
    {
        $entry = $app['entry_api']->getEntryById($id);

        if (empty($entry)) {
            $app->abort(404, "Post $id does not exist.");
        }

        return $app['twig']->render('view.twig', array('entry' => $entry[0]));
    }

    public function addAction(Request $request, Application $app)
    {
        $data = array('url');

        $form = $app['form.factory']->createBuilder('form', $data)
            ->add('url')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $entry = $app['entry_api']->createAndSaveEntryFromUrl($data['url']);

            return $app->redirect('/');
        }

        // display the form
        return $app['twig']->render('add.twig', array('form' => $form->createView()));
    }

    public function removeAction(Request $request, Application $app, $id)
    {
        $entry = $app['entry_api']->remove($id);
        $app['session']->getFlashBag()->add(
                'info',
                array(
                    'title'   => 'success',
                    'message' => 'entry #' . $id . ' removed. <a href="'.$app['url_generator']->generate('restore_entry', array('id' => $id)).'">undo</a>',
                )
        );

        $referer = $request->headers->get('referer');

        return $app->redirect($referer);
    }

    public function restoreAction(Request $request, Application $app, $id)
    {
        $entry = $app['entry_api']->restore($id);
        $referer = $request->headers->get('referer');

        return $app->redirect($referer);
    }
}
