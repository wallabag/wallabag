<?php
use Poche\Model\Entry;

use Symfony\Component\HttpFoundation\Request;

$front = $app['controllers_factory'];
$front->get('/', function () use ($app) {

    $entries = $app['entry_api']->getEntries();

    return $app['twig']->render('index.twig', array('entries' => $entries));
});

$front->get('/view/{id}', function (Request $request, $id) use ($app) {

    $entry = $app['entry_api']->getEntryById($id);

    return $app['twig']->render('view.twig', array('entry' => $entry[0]));
})
->bind('view_entry');

$front->get('/mark-read/{id}', function (Request $request, $id) use ($app) {

    $entry = $app['entry_api']->markAsRead($id);

    return $app->redirect('/view/' . $id);
})
->bind('mark_entry_read');

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

return $front;
