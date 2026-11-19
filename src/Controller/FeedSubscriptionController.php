<?php

namespace Wallabag\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\Feed\Reader\Exception\ExceptionInterface as ReaderException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientException;
use Wallabag\Entity\FeedUrl;
use Wallabag\Form\Type\FeedUrlType;
use Wallabag\Helper\FeedReader;

class FeedSubscriptionController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FeedReader $reader,
    ) {
    }

    #[Route(path: '/atom/list', name: 'all_import_feed', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        return $this->render('FeedSubscription/index.html.twig', [
            'feeds' => $this->entityManager->getRepository(FeedUrl::class)->findBy(
                ['user' => $this->getUser()],
                ['id' => 'ASC']
            ),
        ]);
    }

    #[Route(path: '/atom/new', name: 'new_feed', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): Response
    {
        $feed = new FeedUrl($this->getUser());
        $form = $this->createForm(FeedUrlType::class, $feed)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->resolveFeedUrl($feed);
            $this->entityManager->persist($feed);
            $this->entityManager->flush();
            $this->addFlash('notice', 'feeds.flash.created');

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

    private function resolveFeedUrl(FeedUrl $feed): void
    {
        try {
            $feed->setUrl($this->reader->resolve($feed->getUrl()));
        } catch (ReaderException|HttpClientException) {
            // Keep the submitted URL so the user can retry a temporarily unavailable feed.
        }
    }
}
