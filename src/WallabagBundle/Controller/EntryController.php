<?php

namespace WallabagBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use WallabagBundle\Repository;
use WallabagBundle\Entity\Entries;

class EntryController extends Controller
{
    /**
     * Shows unread entries for current user
     *
     * @Route("/unread", name="unread")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showUnreadAction()
    {
        $repository = $this->getDoctrine()->getRepository('WallabagBundle:Entries');
        $entries = $repository->findUnreadByUser(1, 0);

        return $this->render(
            'WallabagBundle:Entry:entries.html.twig',
            array('entries' => $entries)
        );
    }

    /**
     * Shows read entries for current user
     *
     * @Route("/archive", name="archive")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showArchiveAction()
    {
        $repository = $this->getDoctrine()->getRepository('WallabagBundle:Entries');
        $entries = $repository->findArchiveByUser(1, 0);

        return $this->render(
            'WallabagBundle:Entry:entries.html.twig',
            array('entries' => $entries)
        );
    }

    /**
     * Shows starred entries for current user
     *
     * @Route("/starred", name="starred")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showStarredAction()
    {
        $repository = $this->getDoctrine()->getRepository('WallabagBundle:Entries');
        $entries = $repository->findStarredByUser(1, 0);

        return $this->render(
            'WallabagBundle:Entry:entries.html.twig',
            array('entries' => $entries)
        );
    }

    /**
     * Shows entry content
     *
     * @param Entries $entry
     * @Route("/view/{id}", requirements={"id" = "\d+"}, name="view")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Entries $entry)
    {
        return $this->render(
            'WallabagBundle:Entry:entry.html.twig',
            array('entry' => $entry)
        );
    }

    /**
     * Changes read status for an entry
     *
     * @param Request $request
     * @param Entries $entry
     * @Route("/archive/{id}", requirements={"id" = "\d+"}, name="archive_entry")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function toggleArchiveAction(Request $request, Entries $entry)
    {
        $entry->toggleArchive();
        $this->getDoctrine()->getManager()->flush();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Entry archived'
        );

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Changes favorite status for an entry
     *
     * @param Request $request
     * @param Entries $entry
     * @Route("/star/{id}", requirements={"id" = "\d+"}, name="star_entry")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function toggleStarAction(Request $request, Entries $entry)
    {
        $entry->toggleStar();
        $this->getDoctrine()->getManager()->flush();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Entry starred'
        );

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Deletes entry
     *
     * @param Request $request
     * @param Entries $entry
     * @Route("/delete/{id}", requirements={"id" = "\d+"}, name="delete_entry")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteEntryAction(Request $request, Entries $entry)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($entry);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Entry deleted'
        );

        return $this->redirect($request->headers->get('referer'));
    }
}
