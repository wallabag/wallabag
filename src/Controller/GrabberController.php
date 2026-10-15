<?php

namespace Wallabag\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\Helper\SpecialGrabbers;

class GrabberController extends AbstractController
{
    #[Route(path: '/grabbers', name: 'special_grabbers', methods: ['GET', 'POST'])]
    public function indexAction(Request $request, SpecialGrabbers $grabbers, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $error = null;
        $config = $this->getUser()->getConfig();
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('grabbers', $request->request->get('token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }
            try {
                $id = $request->request->get('id', '');
                $data = ['enabled' => $request->request->getBoolean('enabled')];
                if ('x' === $id) {
                    $data['article_reader_enabled'] = $request->request->getBoolean('article_reader_enabled');
                }
                $grabbers->update($config, $id, $data);
                $em->flush();
                $this->addFlash('notice', 'Special grabbers updated.');

                return $this->redirectToRoute('special_grabbers');
            } catch (\InvalidArgumentException $e) {
                $error = $e->getMessage();
            }
        }

        return $this->render('Grabber/index.html.twig', ['grabbers' => $grabbers->list($config), 'error' => $error]);
    }
}
