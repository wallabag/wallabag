<?php

namespace Wallabag\CoreBundle\Controller;

use Doctrine\ORM\NoResultException;
use Pagerfanta\Doctrine\ORM\QueryAdapter as DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Event\EntryDeletedEvent;
use Wallabag\CoreBundle\Event\EntrySavedEvent;
use Wallabag\CoreBundle\Form\Type\EditEntryType;
use Wallabag\CoreBundle\Form\Type\EntryFilterType;
use Wallabag\CoreBundle\Form\Type\NewEntryType;
use Wallabag\CoreBundle\Form\Type\SearchEntryType;

class EntryController extends Controller
{
    /**
     * @Route("/mass", name="mass_action")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function massAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $values = $request->request->all();

        $action = 'toggle-read';
        if (isset($values['toggle-star'])) {
            $action = 'toggle-star';
        } elseif (isset($values['delete'])) {
            $action = 'delete';
        }

        if (isset($values['entry-checkbox'])) {
            foreach ($values['entry-checkbox'] as $id) {
                /** @var Entry * */
                $entry = $this->get('wallabag_core.entry_repository')->findById((int) $id)[0];

                $this->checkUserAction($entry);

                if ('toggle-read' === $action) {
                    $entry->toggleArchive();
                } elseif ('toggle-star' === $action) {
                    $entry->toggleStar();
                } elseif ('delete' === $action) {
                    $this->get('event_dispatcher')->dispatch(EntryDeletedEvent::NAME, new EntryDeletedEvent($entry));
                    $em->remove($entry);
                }
            }

            $em->flush();
        }

        $redirectUrl = $this->get('wallabag_core.helper.redirect')->to($request->headers->get('referer'));

        return $this->redirect($redirectUrl);
    }

    /**
     * @param int $page
     *
     * @Route("/search/{page}", name="search", defaults={"page" = 1})
     *
     * Default parameter for page is hardcoded (in duplication of the defaults from the Route)
     * because this controller is also called inside the layout template without any page as argument
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchFormAction(Request $request, $page = 1, $currentRoute = null)
    {
        // fallback to retrieve currentRoute from query parameter instead of injected one (when using inside a template)
        if (null === $currentRoute && $request->query->has('currentRoute')) {
            $currentRoute = $request->query->get('currentRoute');
        }

        $form = $this->createForm(SearchEntryType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->showEntries('search', $request, $page);
        }

        return $this->render('WallabagCoreBundle:Entry:search_form.html.twig', [
            'form' => $form->createView(),
            'currentRoute' => $currentRoute,
        ]);
    }

    /**
     * @Route("/new-entry", name="new_entry")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addEntryFormAction(Request $request)
    {
        $entry = new Entry($this->getUser());

        $form = $this->createForm(NewEntryType::class, $entry);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingEntry = $this->checkIfEntryAlreadyExists($entry);

            if (false !== $existingEntry) {
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $this->get('translator')->trans('flashes.entry.notice.entry_already_saved', ['%date%' => $existingEntry->getCreatedAt()->format('d-m-Y')])
                );

                return $this->redirect($this->generateUrl('view', ['id' => $existingEntry->getId()]));
            }

            $this->updateEntry($entry);

            $em = $this->getDoctrine()->getManager();
            $em->persist($entry);
            $em->flush();

            // entry saved, dispatch event about it!
            $this->get('event_dispatcher')->dispatch(EntrySavedEvent::NAME, new EntrySavedEvent($entry));

            return $this->redirect($this->generateUrl('homepage'));
        }

        return $this->render('WallabagCoreBundle:Entry:new_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
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

            $em = $this->getDoctrine()->getManager();
            $em->persist($entry);
            $em->flush();

            // entry saved, dispatch event about it!
            $this->get('event_dispatcher')->dispatch(EntrySavedEvent::NAME, new EntrySavedEvent($entry));
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
     * @Route("/edit/{id}", requirements={"id" = "\d+"}, name="edit")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editEntryAction(Request $request, Entry $entry)
    {
        $this->checkUserAction($entry);

        $form = $this->createForm(EditEntryType::class, $entry);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
     * @param int $page
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
     * @param int $page
     *
     * @Route("/unread/list/{page}", name="unread", defaults={"page" = "1"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showUnreadAction(Request $request, $page)
    {
        // load the quickstart if no entry in database
        if (1 === (int) $page && 0 === $this->get('wallabag_core.entry_repository')->countAllEntriesByUser($this->getUser()->getId())) {
            return $this->redirect($this->generateUrl('quickstart'));
        }

        return $this->showEntries('unread', $request, $page);
    }

    /**
     * Shows read entries for current user.
     *
     * @param int $page
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
     * @param int $page
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
     * Shows untagged articles for current user.
     *
     * @param int $page
     *
     * @Route("/untagged/list/{page}", name="untagged", defaults={"page" = "1"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showUntaggedEntriesAction(Request $request, $page)
    {
        return $this->showEntries('untagged', $request, $page);
    }

    /**
     * Shows random entry depending on the given type.
     *
     * @param string $type
     *
     * @Route("/{type}/random", name="random_entry", requirements={"type": "unread|starred|archive|untagged|all"})
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectRandomEntryAction($type = 'all')
    {
        try {
            $entry = $this->get('wallabag_core.entry_repository')
                ->getRandomEntry($this->getUser()->getId(), $type);
        } catch (NoResultException $e) {
            $bag = $this->get('session')->getFlashBag();
            $bag->clear();
            $bag->add('notice', 'flashes.entry.notice.no_random_entry');

            return $this->redirect($this->generateUrl($type));
        }

        return $this->redirect($this->generateUrl('view', ['id' => $entry->getId()]));
    }

    /**
     * Shows entry content.
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
     * @Route("/reload/{id}", requirements={"id" = "\d+"}, name="reload_entry")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function reloadAction(Entry $entry)
    {
        $this->checkUserAction($entry);

        $this->updateEntry($entry, 'entry_reloaded');

        // if refreshing entry failed, don't save it
        if ($this->getParameter('wallabag_core.fetching_error_message') === $entry->getContent()) {
            $bag = $this->get('session')->getFlashBag();
            $bag->clear();
            $bag->add('notice', 'flashes.entry.notice.entry_reloaded_failed');

            return $this->redirect($this->generateUrl('view', ['id' => $entry->getId()]));
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($entry);
        $em->flush();

        // entry saved, dispatch event about it!
        $this->get('event_dispatcher')->dispatch(EntrySavedEvent::NAME, new EntrySavedEvent($entry));

        return $this->redirect($this->generateUrl('view', ['id' => $entry->getId()]));
    }

    /**
     * Changes read status for an entry.
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
     * @Route("/star/{id}", requirements={"id" = "\d+"}, name="star_entry")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function toggleStarAction(Request $request, Entry $entry)
    {
        $this->checkUserAction($entry);

        $entry->toggleStar();
        $entry->updateStar($entry->isStarred());
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

        // entry deleted, dispatch event about it!
        $this->get('event_dispatcher')->dispatch(EntryDeletedEvent::NAME, new EntryDeletedEvent($entry));

        $em = $this->getDoctrine()->getManager();
        $em->remove($entry);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'flashes.entry.notice.entry_deleted'
        );

        // don't redirect user to the deleted entry (check that the referer doesn't end with the same url)
        $referer = $request->headers->get('referer');
        $to = (1 !== preg_match('#' . $url . '$#i', $referer) ? $referer : null);

        $redirectUrl = $this->get('wallabag_core.helper.redirect')->to($to);

        return $this->redirect($redirectUrl);
    }

    /**
     * Get public URL for entry (and generate it if necessary).
     *
     * @Route("/share/{id}", requirements={"id" = "\d+"}, name="share")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function shareAction(Entry $entry)
    {
        $this->checkUserAction($entry);

        if (null === $entry->getUid()) {
            $entry->generateUid();

            $em = $this->getDoctrine()->getManager();
            $em->persist($entry);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('share_entry', [
            'uid' => $entry->getUid(),
        ]));
    }

    /**
     * Disable public sharing for an entry.
     *
     * @Route("/share/delete/{id}", requirements={"id" = "\d+"}, name="delete_share")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteShareAction(Entry $entry)
    {
        $this->checkUserAction($entry);

        $entry->cleanUid();

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
     * @Route("/share/{uid}", requirements={"uid" = ".+"}, name="share_entry")
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
            '@WallabagCore/themes/common/Entry/share.html.twig',
            ['entry' => $entry]
        );
    }

    /**
     * Global method to retrieve entries depending on the given type
     * It returns the response to be send.
     *
     * @param string $type Entries type: unread, starred or archive
     * @param int    $page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function showEntries($type, Request $request, $page)
    {
        $repository = $this->get('wallabag_core.entry_repository');
        $searchTerm = (isset($request->get('search_entry')['term']) ? $request->get('search_entry')['term'] : '');
        $currentRoute = (null !== $request->query->get('currentRoute') ? $request->query->get('currentRoute') : '');

        switch ($type) {
            case 'search':
                $qb = $repository->getBuilderForSearchByUser($this->getUser()->getId(), $searchTerm, $currentRoute);
                break;
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

        $pagerAdapter = new DoctrineORMAdapter($qb->getQuery(), true, false);

        $entries = $this->get('wallabag_core.helper.prepare_pager_for_entries')->prepare($pagerAdapter);

        try {
            $entries->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException $e) {
            if ($page > 1) {
                return $this->redirect($this->generateUrl($type, ['page' => $entries->getNbPages()]), 302);
            }
        }

        $nbEntriesUntagged = $this->get('wallabag_core.entry_repository')
            ->countUntaggedEntriesByUser($this->getUser()->getId());

        return $this->render(
            'WallabagCoreBundle:Entry:entries.html.twig', [
                'form' => $form->createView(),
                'entries' => $entries,
                'currentPage' => $page,
                'searchTerm' => $searchTerm,
                'isFiltered' => $form->isSubmitted(),
                'nbEntriesUntagged' => $nbEntriesUntagged,
            ]
        );
    }

    /**
     * Fetch content and update entry.
     * In case it fails, $entry->getContent will return an error message.
     *
     * @param string $prefixMessage Should be the translation key: entry_saved or entry_reloaded
     */
    private function updateEntry(Entry $entry, $prefixMessage = 'entry_saved')
    {
        $message = 'flashes.entry.notice.' . $prefixMessage;

        try {
            $this->get('wallabag_core.content_proxy')->updateEntry($entry, $entry->getUrl());
        } catch (\Exception $e) {
            $this->get('logger')->error('Error while saving an entry', [
                'exception' => $e,
                'entry' => $entry,
            ]);

            $message = 'flashes.entry.notice.' . $prefixMessage . '_failed';
        }

        if (empty($entry->getDomainName())) {
            $this->get('wallabag_core.content_proxy')->setEntryDomainName($entry);
        }

        if (empty($entry->getTitle())) {
            $this->get('wallabag_core.content_proxy')->setDefaultEntryTitle($entry);
        }

        $this->get('session')->getFlashBag()->add('notice', $message);
    }

    /**
     * Check if the logged user can manage the given entry.
     */
    private function checkUserAction(Entry $entry)
    {
        if (null === $this->getUser() || $this->getUser()->getId() !== $entry->getUser()->getId()) {
            throw $this->createAccessDeniedException('You can not access this entry.');
        }
    }

    /**
     * Check for existing entry, if it exists, redirect to it with a message.
     *
     * @return Entry|bool
     */
    private function checkIfEntryAlreadyExists(Entry $entry)
    {
        return $this->get('wallabag_core.entry_repository')->findByUrlAndUserId($entry->getUrl(), $this->getUser()->getId());
    }
}
