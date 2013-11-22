<?php
use Poche\Model\Entry;

$front = $app['controllers_factory'];
$front->get('/', function () use ($app) {

    $entry = new Entry(1, "Titre de test");

    return $app['twig']->render('index.twig', array('entry' => $entry));
});


return $front;
