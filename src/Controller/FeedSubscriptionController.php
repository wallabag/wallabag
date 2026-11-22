<?php

namespace Wallabag\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\Feed\Reader\Exception\ExceptionInterface as ReaderException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientException;
use Wallabag\Entity\FeedUrl;
use Wallabag\Form\Type\FeedUrlType;
use Wallabag\Helper\FeedFetcher;
use Wallabag\Helper\FeedReader;
use Wallabag\Repository\FeedItemRepository;

class FeedSubscriptionController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FeedReader $reader,
        private readonly FeedFetcher $fetcher,
    ) {
    }

    #[Route(path: '/atom/list', name: 'all_import_feed', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(FeedItemRepository $items): Response
    {
        $user = $this->getUser() ?? throw $this->createAccessDeniedException();

        return $this->render('FeedSubscription/index.html.twig', [
            'feeds' => $this->entityManager->getRepository(FeedUrl::class)->findBy(
                ['user' => $user],
                ['id' => 'ASC']
            ),
            'pendingFeeds' => array_fill_keys($items->findPendingFeedIds($user), true),
        ]);
    }

    #[Route(path: '/atom/new', name: 'new_feed', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): Response
    {
        $feed = new FeedUrl($this->getUser());
        $form = $this->createForm(FeedUrlType::class, $feed)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($feed);
            $this->entityManager->flush();
            $this->addFlash('notice', $this->refresh($feed)
                ? 'feeds.flash.created'
                : 'feeds.flash.created_refresh_error');

            return $this->redirectToRoute('all_import_feed');
        }

        return $this->render('FeedSubscription/form.html.twig', [
            'form' => $form->createView(),
            'title' => 'feeds.action.add',
        ]);
    }

    #[Route(path: '/atom/edit/{id}', name: 'edit_feed', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('EDIT', subject: 'feed')]
    public function edit(Request $request, FeedUrl $feed): Response
    {
        $previousUrl = $feed->getUrl();
        $form = $this->createForm(FeedUrlType::class, $feed)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($previousUrl !== $feed->getUrl()) {
                $this->resolveFeedUrl($feed);
            }
            $this->entityManager->flush();
            $this->addFlash('notice', 'feeds.flash.updated');

            return $this->redirectToRoute('all_import_feed');
        }

        return $this->render('FeedSubscription/form.html.twig', [
            'form' => $form->createView(),
            'title' => 'feeds.action.edit',
        ]);
    }

    #[Route(path: '/atom/fetch/{id}', name: 'fetch_feed', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('REFRESH', subject: 'feed')]
    public function fetch(Request $request, FeedUrl $feed): Response
    {
        $token = $request->request->all()['token'] ?? null;

        if (!\is_string($token) || !$this->isCsrfTokenValid('feed-fetch-' . $feed->getId(), $token)) {
            throw new BadRequestHttpException('Invalid CSRF token.');
        }

        $this->addFlash('notice', $this->refresh($feed) ? 'feeds.flash.fetched' : 'feeds.flash.fetch_error');

        if ($feed->isAutomatic()) {
            return $this->redirectToRoute('all_import_feed');
        }

        return $this->redirectToRoute('display_feed_items', ['id' => $feed->getId()]);
    }

    #[Route(path: '/atom/delete/{id}', name: 'delete_feed', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('DELETE', subject: 'feed')]
    public function delete(Request $request, FeedUrl $feed): Response
    {
        $token = $request->request->all()['token'] ?? null;

        if (!\is_string($token) || !$this->isCsrfTokenValid('feed-delete-' . $feed->getId(), $token)) {
            throw new BadRequestHttpException('Invalid CSRF token.');
        }

        $this->entityManager->remove($feed);
        $this->entityManager->flush();
        $this->addFlash('notice', 'feeds.flash.deleted');

        return $this->redirectToRoute('all_import_feed');
    }

    private function resolveFeedUrl(FeedUrl $feed): void
    {
        try {
            $feed->setUrl($this->reader->resolve($feed->getUrl()));
        } catch (ReaderException|HttpClientException) {
            // Keep the submitted URL so the user can retry a temporarily unavailable feed.
        }
    }

    private function refresh(FeedUrl $feed): bool
    {
        try {
            $this->fetcher->fetch($feed);

            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
