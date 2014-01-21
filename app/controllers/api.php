<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

$api = $app['controllers_factory'];

$api->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

$api->get('/', function () { return 'API home page'; });

$api->get('/entries', function () use ($app) {
    $entries = $app['entry_api']->getEntries('unread');
    return $app->json($entries, 200);
});

$api->post('/entries', function (Request $request) use ($app) {
    $url = $request->request->get('url');

    $entry = $app['entry_api']->createEntryFromUrl($url);

    return $app->json($entry, 201);
});

$api->get('/archives', function () use ($app) {
    $entries = $app['entry_api']->getEntries('read');
    return $app->json($entries, 200);
});

$api->get('/bookmarks', function () use ($app) {
    $entries = $app['entry_api']->getBookmarks();
    return $app->json($entries, 200);
});

$api->get('/get', function (Request $request) use ($app) {
    $id = $request->request->get('id');

    $entry = $app['entry_api']->getEntryById($id);

    return $app->json($entry, 201);
});

$api->get('/mark-read', function (Request $request) use ($app) {
    $id = $request->request->get('id');

    $entry = $app['entry_api']->markAsRead($id);

    return $app->json($entry, 201);
});

$api->get('/mark-unread', function (Request $request) use ($app) {
    $id = $request->request->get('id');

    $entry = $app['entry_api']->markAsUnread($id);

    return $app->json($entry, 201);
});

$api->get('/star', function (Request $request) use ($app) {
    $id = $request->request->get('id');

    $entry = $app['entry_api']->star($id);

    return $app->json($entry, 201);
});

$api->get('/unstar', function (Request $request) use ($app) {
    $id = $request->request->get('id');

    $entry = $app['entry_api']->unstar($id);

    return $app->json($entry, 201);
});

$api->get('/remove', function (Request $request) use ($app) {
    $id = $request->request->get('id');

    $entry = $app['entry_api']->remove($id);

    return $app->json($entry, 201);
});
$api->get('/restore', function (Request $request) use ($app) {
    $id = $request->request->get('id');

    $entry = $app['entry_api']->restore($id);

    return $app->json($entry, 201);
});
return $api;
