<?php
use Poche\Model\Entry;

$app->get('/', function () use ($app) {

    $entry = new Entry(1, "Titre de test");

    return $app['twig']->render('index.twig', array('entry' => $entry));
});
