<?php
use Poche\Model\Entry;

use Symfony\Component\HttpFoundation\Request;

$front = $app['controllers_factory'];
$front->get('/', function () use ($app) {

    $entry = new Entry(1, "Titre de test");

    return $app['twig']->render('index.twig', array('entry' => $entry));
});

$front->match('/add', function (Request $request) use ($app) {
    $data = array('url');

    $form = $app['form.factory']->createBuilder('form', $data)
        ->add('url')
        ->getForm();

    $form->handleRequest($request);

    if ($form->isValid()) {
        $data = $form->getData();

        // do something with the url
        
        return $app->redirect('/');
    }

    // display the form
    return $app['twig']->render('add.twig', array('form' => $form->createView()));
});

return $front;
