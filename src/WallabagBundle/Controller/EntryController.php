<?php

namespace WallabagBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use WallabagBundle\Repository;

class EntryController extends Controller
{
    /**
     * @Route("/unread", name="unread")
     */
    public function showUnreadAction()
    {
        $repository = $this->getDoctrine()->getRepository('WallabagBundle:Entries');
        $entries = $repository->findUnreadByUser(1);

        return $this->render(
            'WallabagBundle:Entry:entries.html.twig',
            array('entries' => $entries)
        );

    }

    /**
     * @Route("/archive", name="archive")
     */
    public function showArchiveAction()
    {
        $repository = $this->getDoctrine()->getRepository('WallabagBundle:Entries');
        $entries = $repository->findArchiveByUser(1);

        return $this->render(
            'WallabagBundle:Entry:entries.html.twig',
            array('entries' => $entries)
        );

    }

    /**
     * @Route("/starred", name="starred")
     */
    public function showStarredAction()
    {
        $repository = $this->getDoctrine()->getRepository('WallabagBundle:Entries');
        $entries = $repository->findStarredByUser(1);

        return $this->render(
            'WallabagBundle:Entry:entries.html.twig',
            array('entries' => $entries)
        );

    }

    /**
     * @Route("/view/{id}", requirements={"id" = "\d+"}, name="view")
     */
    public function viewAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WallabagBundle:Entries');
        $entry = $repository->find($id);

        return $this->render(
            'WallabagBundle:Entry:entry.html.twig',
            array('entry' => $entry)
        );

    }
}
