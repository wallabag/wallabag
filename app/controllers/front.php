<?php
use Poche\Model\Entry;

use Symfony\Component\HttpFoundation\Request;

$front = $app['controllers_factory'];
$front->get('/', function () use ($app) {

    $entries = $app['entry_api']->getEntries('unread');

    return $app['twig']->render('index.twig', array('entries' => $entries));
});

$front->get('/view/{id}', function (Request $request, $id) use ($app) {

    $entry = $app['entry_api']->getEntryById($id);

    return $app['twig']->render('view.twig', array('entry' => $entry[0]));
})
->bind('view_entry');

$front->get('/mark-read/{id}', function (Request $request, $id) use ($app) {

    $entry = $app['entry_api']->markAsRead($id);

    $referer = $request->headers->get('referer');

    return $app->redirect($referer);
})
->bind('mark_entry_read');

$front->get('/mark-unread/{id}', function (Request $request, $id) use ($app) {

    $entry = $app['entry_api']->markAsUnread($id);

    $referer = $request->headers->get('referer');

    return $app->redirect($referer);
})
->bind('mark_entry_unread');

$front->get('/star/{id}', function (Request $request, $id) use ($app) {

    $entry = $app['entry_api']->star($id);

    $referer = $request->headers->get('referer');

    return $app->redirect($referer);
})
->bind('star_entry');

$front->get('/unstar/{id}', function (Request $request, $id) use ($app) {

    $entry = $app['entry_api']->unstar($id);

    $referer = $request->headers->get('referer');

    return $app->redirect($referer);
})
->bind('unstar_entry');

$front->match('/add', function (Request $request) use ($app) {
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
})
->bind('add');

$front->get('/archive', function () use ($app) {

    $entries = $app['entry_api']->getEntries('read');

    return $app['twig']->render('archive.twig', array('entries' => $entries));
});

return $front;
