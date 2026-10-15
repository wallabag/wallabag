<?php

namespace Wallabag\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\Helper\SpecialGrabbers;

class GrabberRestController extends WallabagRestController
{
    #[Route(path: '/api/grabbers.{_format}', methods: ['GET'], name: 'api_grabbers', defaults: ['_format' => 'json'])]
    public function listAction(SpecialGrabbers $grabbers): JsonResponse
    {
        $this->validateAuthentication();

        return new JsonResponse($grabbers->list($this->getUser()->getConfig()));
    }

    #[Route(path: '/api/grabbers/{id}.{_format}', methods: ['PATCH'], name: 'api_grabber_update', defaults: ['_format' => 'json'])]
    public function updateAction(string $id, Request $request, SpecialGrabbers $grabbers): JsonResponse
    {
        $this->validateAuthentication();
        try {
            $data = \strlen($request->getContent()) <= 2048 ? json_decode($request->getContent(), true) : null;
            if (!\is_array($data)) {
                throw new \InvalidArgumentException('Expected a JSON object up to 2 KiB.');
            }
            $grabbers->update($this->getUser()->getConfig(), $id, $data);
            $this->entityManager->flush();

            return new JsonResponse($grabbers->list($this->getUser()->getConfig()));
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}
