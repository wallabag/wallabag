<?php

namespace Wallabag\CoreBundle\Controller;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Form\Type\EntryFilterType;
use Wallabag\CoreBundle\Form\Type\EditEntryType;
use Wallabag\CoreBundle\Form\Type\NewEntryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

class EntryController extends Controller
{
    /**
     * @param Entry $entry
     */
    private function updateEntry(Entry $entry)
    {
        try {
            $entry = $this->get('wallabag_core.content_proxy')->updateEntry($entry, $entry->getUrl());

            $em = $this->getDoctrine()->getManager();
            $em->persist($entry);
            $em->flush();
        } catch (\Exception $e) {
            $this->get('logger')->error('Error while saving an entry', [
                'exception' => $e,
                'entry' => $entry,
            ]);

            return false;
        }

        return true;
    }

    /**
     * @param Request $request
     *
     * @Route("/new-entry", name="new_entry")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addEntryFormAction(Request $request)
    {
        $entry = new Entry($this->getUser());

        $form = $this->createForm(NewEntryType::class, $entry);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $existingEntry = $this->checkIfEntryAlreadyExists($entry);

            if (false !== $existingEntry) {
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $this->get('translator')->trans('flashes.entry.notice.entry_already_saved', ['%date%' => $existingEntry->getCreatedAt()->format('d-m-Y')])
                );

                return $this->redirect($this->generateUrl('view', ['id' => $existingEntry->getId()]));
            }

            $message = 'flashes.entry.notice.entry_saved';
            if (false === $this->updateEntry($entry)) {
                $message = 'flashes.entry.notice.entry_saved_failed';
            }

            $this->get('session')->getFlashBag()->add('notice', $message);

            return $this->redirect($this->generateUrl('homepage'));
        }

        return $this->render('WallabagCoreBundle:Entry:new_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @Route("/bookmarklet", name="bookmarklet")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addEntryViaBookmarkletAction(Request $request)
    {
        $entry = new Entry($this->getUser());
        $entry->setUrl($request->get('url'));

        if (false === $this->checkIfEntryAlreadyExists($entry)) {
            $this->updateEntry($entry);
        }

        return $this->redirect($this->generateUrl('homepage'));
    }

    /**
     * @Route("/new", name="new")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addEntryAction()
    {
        return $this->render('WallabagCoreBundle:Entry:new.html.twig');
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

        $form = $this->createForm(EditEntryType::class, $entry);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entry);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'flashes.entry.notice.entry_updated'
            );

            return $this->redirect($this->generateUrl('view', ['id' => $entry->getId()]));
        }

        return $this->render('WallabagCoreBundle:Entry:edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Shows all entries for current user.
     *
     * @param Request $request
     * @param int     $page
     *
     * @Route("/all/list/{page}", name="all", defaults={"page" = "1"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAllAction(Request $request, $page)
    {
        return $this->showEntries('all', $request, $page);
    }

    /**
     * Shows unread entries for current user.
     *
     * @param Request $request
     * @param int     $page
     *
     * @Route("/unread/list/{page}", name="unread", defaults={"page" = "1"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showUnreadAction(Request $request, $page)
    {
        // load the quickstart if no entry in database
        if ($page == 1 && $this->get('wallabag_core.entry_repository')->countAllEntriesByUsername($this->getUser()->getId()) == 0) {
            return $this->redirect($this->generateUrl('quickstart'));
        }

        return $this->showEntries('unread', $request, $page);
    }

    /**
     * Shows read entries for current user.
     *
     * @param Request $request
     * @param int     $page
     *
     * @Route("/archive/list/{page}", name="archive", defaults={"page" = "1"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showArchiveAction(Request $request, $page)
    {
        return $this->showEntries('archive', $request, $page);
    }

    /**
     * Shows starred entries for current user.
     *
     * @param Request $request
     * @param int     $page
     *
     * @Route("/starred/list/{page}", name="starred", defaults={"page" = "1"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showStarredAction(Request $request, $page)
    {
        return $this->showEntries('starred', $request, $page);
    }

    /**
     * Global method to retrieve entries depending on the given type
     * It returns the response to be send.
     *
     * @param string  $type    Entries type: unread, starred or archive
     * @param Request $request
     * @param int     $page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function showEntries($type, Request $request, $page)
    {
        $repository = $this->get('wallabag_core.entry_repository');

        switch ($type) {
            case 'untagged':
                $qb = $repository->getBuilderForUntaggedByUser($this->getUser()->getId());

                break;
            case 'starred':
                $qb = $repository->getBuilderForStarredByUser($this->getUser()->getId());
                break;

            case 'archive':
                $qb = $repository->getBuilderForArchiveByUser($this->getUser()->getId());
                break;

            case 'unread':
                $qb = $repository->getBuilderForUnreadByUser($this->getUser()->getId());
                break;

            case 'all':
                $qb = $repository->getBuilderForAllByUser($this->getUser()->getId());
                break;

            default:
                throw new \InvalidArgumentException(sprintf('Type "%s" is not implemented.', $type));
        }

        $form = $this->createForm(EntryFilterType::class);

        if ($request->query->has($form->getName())) {
            // manually bind values from the request
            $form->submit($request->query->get($form->getName()));

            // build the query from the given form object
            $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $qb);
        }

        $pagerAdapter = new DoctrineORMAdapter($qb->getQuery());

        $entries = $this->get('wallabag_core.helper.prepare_pager_for_entries')
            ->prepare($pagerAdapter, $page);

        try {
            $entries->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException $e) {
            if ($page > 1) {
                return $this->redirect($this->generateUrl($type, ['page' => $entries->getNbPages()]), 302);
            }
        }

        return $this->render(
            'WallabagCoreBundle:Entry:entries.html.twig',
            [
                'form' => $form->createView(),
                'entries' => $entries,
                'currentPage' => $page,
            ]
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
            ['entry' => $entry]
        );
    }

    /**
     * Reload an entry.
     * Refetch content from the website and make it readable again.
     *
     * @param Entry $entry
     *
     * @Route("/reload/{id}", requirements={"id" = "\d+"}, name="reload_entry")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function reloadAction(Entry $entry)
    {
        $this->checkUserAction($entry);

        $message = 'flashes.entry.notice.entry_reloaded';
        if (false === $this->updateEntry($entry)) {
            $message = 'flashes.entry.notice.entry_reload_failed';
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            $message
        );

        return $this->redirect($this->generateUrl('view', ['id' => $entry->getId()]));
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

        $message = 'flashes.entry.notice.entry_unarchived';
        if ($entry->isArchived()) {
            $message = 'flashes.entry.notice.entry_archived';
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            $message
        );

        $redirectUrl = $this->get('wallabag_core.helper.redirect')->to($request->headers->get('referer'));

        return $this->redirect($redirectUrl);
    }

    /**
     * Changes starred status for an entry.
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

        $message = 'flashes.entry.notice.entry_unstarred';
        if ($entry->isStarred()) {
            $message = 'flashes.entry.notice.entry_starred';
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            $message
        );

        $redirectUrl = $this->get('wallabag_core.helper.redirect')->to($request->headers->get('referer'));

        return $this->redirect($redirectUrl);
    }

    /**
     * Deletes entry and redirect to the homepage or the last viewed page.
     *
     * @param Entry $entry
     *
     * @Route("/delete/{id}", requirements={"id" = "\d+"}, name="delete_entry")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteEntryAction(Request $request, Entry $entry)
    {
        $this->checkUserAction($entry);

        // generates the view url for this entry to check for redirection later
        // to avoid redirecting to the deleted entry. Ugh.
        $url = $this->generateUrl(
            'view',
            ['id' => $entry->getId()],
            UrlGeneratorInterface::ABSOLUTE_PATH
        );

        $em = $this->getDoctrine()->getManager();
        $em->remove($entry);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'flashes.entry.notice.entry_deleted'
        );

        // don't redirect user to the deleted entry (check that the referer doesn't end with the same url)
        $referer = $request->headers->get('referer');
        $to = (1 !== preg_match('#'.$url.'$#i', $referer) ? $referer : null);

        $redirectUrl = $this->get('wallabag_core.helper.redirect')->to($to);

        return $this->redirect($redirectUrl);
    }

    /**
     * Check if the logged user can manage the given entry.
     *
     * @param Entry $entry
     */
    private function checkUserAction(Entry $entry)
    {
        if (null === $this->getUser() || $this->getUser()->getId() != $entry->getUser()->getId()) {
            throw $this->createAccessDeniedException('You can not access this entry.');
        }
    }

    /**
     * Check for existing entry, if it exists, redirect to it with a message.
     *
     * @param Entry $entry
     *
     * @return Entry|bool
     */
    private function checkIfEntryAlreadyExists(Entry $entry)
    {
        return $this->get('wallabag_core.entry_repository')->findByUrlAndUserId($entry->getUrl(), $this->getUser()->getId());
    }

    /**
     * Get public URL for entry (and generate it if necessary).
     *
     * @param Entry $entry
     *
     * @Route("/share/{id}", requirements={"id" = "\d+"}, name="share")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function shareAction(Entry $entry)
    {
        $this->checkUserAction($entry);

        if (null === $entry->getUuid()) {
            $entry->generateUuid();

            $em = $this->getDoctrine()->getManager();
            $em->persist($entry);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('share_entry', [
            'uuid' => $entry->getUuid(),
        ]));
    }

    /**
     * Disable public sharing for an entry.
     *
     * @param Entry $entry
     *
     * @Route("/share/delete/{id}", requirements={"id" = "\d+"}, name="delete_share")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteShareAction(Entry $entry)
    {
        $this->checkUserAction($entry);

        $entry->cleanUuid();

        $em = $this->getDoctrine()->getManager();
        $em->persist($entry);
        $em->flush();

        return $this->redirect($this->generateUrl('view', [
            'id' => $entry->getId(),
        ]));
    }

    /**
     * Ability to view a content publicly.
     *
     * @param Entry $entry
     *
     * @Route("/share/{uuid}", requirements={"uuid" = ".+"}, name="share_entry")
     * @Cache(maxage="25200", smaxage="25200", public=true)
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function shareEntryAction(Entry $entry)
    {
        if (!$this->get('craue_config')->get('share_public')) {
            throw $this->createAccessDeniedException('Sharing an entry is disabled for this user.');
        }

        return $this->render(
            '@WallabagCore/themes/share.html.twig',
            ['entry' => $entry]
        );
    }

    /**
     * Shows untagged articles for current user.
     *
     * @param Request $request
     * @param int     $page
     *
     * @Route("/untagged/list/{page}", name="untagged", defaults={"page" = "1"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showUntaggedEntriesAction(Request $request, $page)
    {
        return $this->showEntries('untagged', $request, $page);
    }
}
