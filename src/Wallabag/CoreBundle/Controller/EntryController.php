<?php

namespace Wallabag\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Service\Extractor;
use Wallabag\CoreBundle\Form\Type\NewEntryType;
use Wallabag\CoreBundle\Form\Type\EditEntryType;

class EntryController extends Controller
{
    /**
     * @param Request $request
     *
     * @Route("/new", name="new_entry")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addEntryAction(Request $request)
    {
        $entry = new Entry($this->getUser());

        $form = $this->createForm(new NewEntryType(), $entry);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $content = Extractor::extract($entry->getUrl());

            $entry->setTitle($content->getTitle());
            $entry->setContent($content->getBody());

            $em = $this->getDoctrine()->getManager();
            $em->persist($entry);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Entry saved'
            );

            return $this->redirect($this->generateUrl('homepage'));
        }

        return $this->render('WallabagCoreBundle:Entry:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Edit an entry content.
     *
     * @param Request $request
     * @param Entry   $entry
     *
     * @Route("/edit/{id}", requirements={"id" = "\d+"}, name="edit")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editEntryAction(Request $request, Entry $entry)
    {
        $this->checkUserAction($entry);

        $form = $this->createForm(new EditEntryType(), $entry);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entry);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Entry updated'
            );

            return $this->redirect($this->generateUrl('view', array('id' => $entry->getId())));
        }

        return $this->render('WallabagCoreBundle:Entry:edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Shows unread entries for current user.
     *
     * @Route("/unread/list/{page}", name="unread", defaults={"page" = "1"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showUnreadAction($page)
    {
        $entries = $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entry')
            ->findUnreadByUser($this->getUser()->getId());

        $entries->setCurrentPage($page);

        return $this->render(
            'WallabagCoreBundle:Entry:entries.html.twig',
            array(
                'entries'       => $entries,
                'currentPage'   => $page
            )
        );
    }

    /**
     * Shows read entries for current user.
     *
     * @Route("/archive/list/{page}", name="archive", defaults={"page" = "1"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showArchiveAction($page)
    {
        $entries = $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entry')
            ->findArchiveByUser($this->getUser()->getId());

        $entries->setCurrentPage($page);

        return $this->render(
            'WallabagCoreBundle:Entry:entries.html.twig',
            array(
                'entries'       => $entries,
                'currentPage'   => $page
            )
        );
    }

    /**
     * Shows starred entries for current user.
     *
     * @Route("/starred/list/{page}", name="starred", defaults={"page" = "1"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showStarredAction($page)
    {
        $entries = $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entry')
            ->findStarredByUser($this->getUser()->getId());

        $entries->setCurrentPage($page);

        return $this->render(
            'WallabagCoreBundle:Entry:entries.html.twig',
            array(
                'entries'       => $entries,
                'currentPage'   => $page
            )
        );
    }

    /**
     * Shows entry content.
     *
     * @param Entry $entry
     *
     * @Route("/view/{id}", requirements={"id" = "\d+"}, name="view")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Entry $entry)
    {
        $this->checkUserAction($entry);

        return $this->render(
            'WallabagCoreBundle:Entry:entry.html.twig',
            array('entry' => $entry)
        );
    }

    /**
     * Changes read status for an entry.
     *
     * @param Request $request
     * @param Entry   $entry
     *
     * @Route("/archive/{id}", requirements={"id" = "\d+"}, name="archive_entry")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function toggleArchiveAction(Request $request, Entry $entry)
    {
        $this->checkUserAction($entry);

        $entry->toggleArchive();
        $this->getDoctrine()->getManager()->flush();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Entry archived'
        );

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Changes favorite status for an entry.
     *
     * @param Request $request
     * @param Entry   $entry
     *
     * @Route("/star/{id}", requirements={"id" = "\d+"}, name="star_entry")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function toggleStarAction(Request $request, Entry $entry)
    {
        $this->checkUserAction($entry);

        $entry->toggleStar();
        $this->getDoctrine()->getManager()->flush();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Entry starred'
        );

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Deletes entry.
     *
     * @param Request $request
     * @param Entry   $entry
     *
     * @Route("/delete/{id}", requirements={"id" = "\d+"}, name="delete_entry")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteEntryAction(Request $request, Entry $entry)
    {
        $this->checkUserAction($entry);

        $em = $this->getDoctrine()->getManager();
        $em->remove($entry);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Entry deleted'
        );

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Check if the logged user can manage the given entry.
     *
     * @param Entry $entry
     */
    private function checkUserAction(Entry $entry)
    {
        if ($this->getUser()->getId() != $entry->getUser()->getId()) {
            throw $this->createAccessDeniedException('You can not access this entry.');
        }
    }
}
