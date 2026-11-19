<?php

namespace Wallabag\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\Entity\FeedUrl;
use Wallabag\Form\Type\FeedUrlType;

class FeedSubscriptionController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
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
}
