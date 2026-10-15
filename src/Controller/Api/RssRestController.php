<?php

namespace Wallabag\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\Rss\FeedManager;

/** Feed endpoints use the existing OAuth firewall and always scope IDs to the authenticated user. */
class RssRestController extends WallabagRestController
{
    #[Route(path: '/api/rss/feeds.{_format}', methods: ['GET', 'POST'], name: 'api_rss_feeds', defaults: ['_format' => 'json'])]
    public function feedsAction(Request $request, FeedManager $feeds): JsonResponse
    {
        $this->validateAuthentication();
        if ($request->isMethod('GET')) {
            return new JsonResponse(array_map(static function ($feed) { return $feed->toArray(); }, $feeds->list($this->getUser())));
        }
        try { return new JsonResponse($feeds->save($this->getUser(), $this->body($request))->toArray(), 201); }
        catch (\InvalidArgumentException $e) { return new JsonResponse(['error' => $e->getMessage()], 400); }
    }

    #[Route(path: '/api/rss/feeds/{id}.{_format}', requirements: ['id' => '\\d+'], methods: ['GET', 'PATCH', 'DELETE'], name: 'api_rss_feed', defaults: ['_format' => 'json'])]
    public function feedAction(Request $request, int $id, FeedManager $feeds): JsonResponse
    {
        $this->validateAuthentication();
        $feed = $feeds->owned($this->getUser(), $id);
        if ($request->isMethod('DELETE')) { $feeds->delete($feed); return new JsonResponse(null, 204); }
        try {
            if ($request->isMethod('PATCH')) { $feed = $feeds->save($this->getUser(), $this->body($request), $feed); }
            return new JsonResponse($feed->toArray());
        } catch (\InvalidArgumentException $e) { return new JsonResponse(['error' => $e->getMessage()], 400); }
    }

    #[Route(path: '/api/rss/import.{_format}', methods: ['POST'], name: 'api_rss_import', defaults: ['_format' => 'json'])]
    public function importAction(Request $request, FeedManager $feeds): JsonResponse
    {
        $this->validateAuthentication();
        try { return new JsonResponse($feeds->import($this->getUser(), $this->body($request))); }
        catch (\InvalidArgumentException $e) { return new JsonResponse(['error' => $e->getMessage()], 400); }
    }

    private function body(Request $request): array
    {
        if (strlen($request->getContent()) > 8 * 1024 * 1024) { throw new \InvalidArgumentException('Import/request limit is 8 MiB.'); }
        $data = json_decode($request->getContent(), true);
        if (JSON_ERROR_NONE !== json_last_error() || !is_array($data)) { throw new \InvalidArgumentException('Expected a JSON object.'); }
        return $data;
    }
}
