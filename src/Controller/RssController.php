<?php

namespace Wallabag\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\Rss\FeedManager;

class RssController extends AbstractController
{
    #[Route(path: '/rss/subscriptions', name: 'rss_subscriptions', methods: ['GET', 'POST'])]
    public function indexAction(Request $request, FeedManager $feeds)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('rss', $request->request->get('token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }
            try {
                $id = $request->request->getInt('id');
                $feed = $id ? $feeds->owned($this->getUser(), $id) : null;
                $action = $request->request->get('action', 'save');
                if ('delete' === $action && $feed) {
                    $feeds->delete($feed);
                } elseif ('import' === $action) {
                    $json = $request->request->get('import_json', '');
                    $data = \strlen($json) <= 8 * 1024 * 1024 ? json_decode($json, true) : null;
                    if (!\is_array($data)) {
                        throw new \InvalidArgumentException('Provide a valid JSON export up to 8 MiB.');
                    }
                    $feeds->import($this->getUser(), $data);
                } else {
                    $data = ['name' => $request->request->get('name', ''), 'tags' => array_values(array_filter(array_map('trim', explode(',', $request->request->get('tags', ''))))), 'max_items' => $request->request->getInt('max_items', 10), 'enabled' => $request->request->getBoolean('enabled')];
                    if (!$feed) {
                        $data['url'] = $request->request->get('url', '');
                    }
                    $feeds->save($this->getUser(), $data, $feed);
                }
                $this->addFlash('notice', 'RSS subscriptions updated.');

                return $this->redirectToRoute('rss_subscriptions');
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('notice', $e->getMessage());
            }
        }
        return $this->render('Rss/index.html.twig', ['feeds' => $feeds->list($this->getUser())]);
    }
}
