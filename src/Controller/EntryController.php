<?php

namespace Wallabag\Controller;

use Craue\ConfigBundle\Util\Config;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Pagerfanta\Doctrine\ORM\QueryAdapter as DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Spiriit\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wallabag\Entity\Entry;
use Wallabag\Entity\Tag;
use Wallabag\Event\EntryDeletedEvent;
use Wallabag\Event\EntrySavedEvent;
use Wallabag\Form\Type\EditEntryType;
use Wallabag\Form\Type\EntryFilterType;
use Wallabag\Form\Type\NewEntryType;
use Wallabag\Form\Type\SearchEntryType;
use Wallabag\Helper\ContentProxy;
use Wallabag\Helper\PreparePagerForEntries;
use Wallabag\Helper\Redirect;
use Wallabag\Repository\EntryRepository;
use Wallabag\Repository\TagRepository;

class EntryController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntryRepository $entryRepository,
        private readonly Redirect $redirectHelper,
        private readonly PreparePagerForEntries $preparePagerForEntriesHelper,
        private readonly FilterBuilderUpdaterInterface $filterBuilderUpdater,
        private readonly ContentProxy $contentProxy,
        private readonly Security $security,
    ) {
    }

    /**
     * @return Response
     */
    #[Route(path: '/mass', name: 'mass_action', methods: ['POST'])]
    #[IsGranted('EDIT_ENTRIES')]
    public function massAction(Request $request, TagRepository $tagRepository)
    {
        if (!$this->isCsrfTokenValid('mass-action', $request->request->get('token'))) {
            throw new BadRequestHttpException('Bad CSRF token.');
        }

        $values = $request->request->all();

        $tagsToAdd = [];
        $tagsToRemove = [];

        $action = 'toggle-read';
        if (isset($values['toggle-star'])) {
            $action = 'toggle-star';
        } elseif (isset($values['delete'])) {
            $action = 'delete';
        } elseif (isset($values['tag'])) {
            $action = 'tag';

            if (isset($values['tags'])) {
                $labels = array_filter(explode(',', (string) $values['tags']),
                    function ($v) {
                        $v = trim($v);

                        return '' !== $v;
                    });
                foreach ($labels as $label) {
                    $remove = false;
                    if (str_starts_with($label, '-')) {
                        $label = substr($label, 1);
                        $remove = true;
                    }
                    $tag = $tagRepository->findOneByLabel($label);
                    if ($remove) {
                        if (null !== $tag) {
                            $tagsToRemove[] = $tag;
                        }
                    } else {
                        if (null === $tag) {
                            $tag = new Tag();
                            $tag->setLabel($label);
                        }
                        $tagsToAdd[] = $tag;
                    }
                }
            }
        }

        if (isset($values['entry-checkbox'])) {
            foreach ($values['entry-checkbox'] as $id) {
                /** @var Entry * */
                $entry = $this->entryRepository->findById([(int) $id])[0];

                if (!$this->security->isGranted('EDIT', $entry)) {
                    throw $this->createAccessDeniedException('You can not access this entry.');
                }

                if ('toggle-read' === $action) {
                    $entry->toggleArchive();
                } elseif ('toggle-star' === $action) {
                    $entry->toggleStar();
                } elseif ('tag' === $action) {
                    foreach ($tagsToAdd as $tag) {
                        $entry->addTag($tag);
                    }
                    foreach ($tagsToRemove as $tag) {
                        $entry->removeTag($tag);
                    }
                } elseif ('delete' === $action) {
                    $this->eventDispatcher->dispatch(new EntryDeletedEvent($entry), EntryDeletedEvent::NAME);
                    $this->entityManager->remove($entry);
                }
            }

            $this->entityManager->flush();
        }

        $redirectUrl = $this->redirectHelper->to($request->query->get('redirect'));

        return $this->redirect($redirectUrl);
    }

    /**
     * @param int $page
     *
     * Default parameter for page is hardcoded (in duplication of the defaults from the Route)
     * because this controller is also called inside the layout template without any page as argument
     * @return Response
     */
    #[Route(path: '/search/{page}', name: 'search', methods: ['GET', 'POST'], defaults: ['page' => 1])]
    #[IsGranted('LIST_ENTRIES')]
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

        return $this->render('Entry/search_form.html.twig', [
            'form' => $form->createView(),
            'currentRoute' => $currentRoute,
        ]);
    }

    /**
     * @return Response
     */
    #[Route(path: '/new-entry', name: 'new_entry', methods: ['GET', 'POST'])]
    #[IsGranted('CREATE_ENTRIES')]
    public function addEntryFormAction(Request $request, TranslatorInterface $translator)
    {
        $entry = new Entry($this->getUser());

        $form = $this->createForm(NewEntryType::class, $entry);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingEntry = $this->checkIfEntryAlreadyExists($entry);

            if (false !== $existingEntry) {
                $this->addFlash(
                    'notice',
                    $translator->trans('flashes.entry.notice.entry_already_saved', ['%date%' => $existingEntry->getCreatedAt()->format('d-m-Y')])
                );

                return $this->redirect($this->generateUrl('view', ['id' => $existingEntry->getId()]));
            }

            $this->updateEntry($entry);

            $this->entityManager->persist($entry);
            $this->entityManager->flush();

            // entry saved, dispatch event about it!
            $this->eventDispatcher->dispatch(new EntrySavedEvent($entry), EntrySavedEvent::NAME);

            return $this->redirect($this->generateUrl('homepage'));
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        return $this->render('Entry/new_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @return Response
     */
    #[Route(path: '/bookmarklet', name: 'bookmarklet', methods: ['GET'])]
    #[IsGranted('CREATE_ENTRIES')]
    public function addEntryViaBookmarkletAction(Request $request)
    {
        $entry = new Entry($this->getUser());
        $entry->setUrl($request->query->get('url'));

        if (false === $this->checkIfEntryAlreadyExists($entry)) {
            $this->updateEntry($entry);

            $this->entityManager->persist($entry);
            $this->entityManager->flush();

            // entry saved, dispatch event about it!
            $this->eventDispatcher->dispatch(new EntrySavedEvent($entry), EntrySavedEvent::NAME);
        }

        return $this->redirect($this->generateUrl('homepage'));
    }

    /**
     * @return Response
     */
    #[Route(path: '/new', name: 'new', methods: ['GET'])]
    #[IsGranted('CREATE_ENTRIES')]
    public function addEntryAction()
    {
        return $this->render('Entry/new.html.twig');
    }

    /**
     * Edit an entry content.
     *
     * @return Response
     */
    #[Route(path: '/edit/{id}', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('EDIT', subject: 'entry')]
    public function editEntryAction(Request $request, Entry $entry)
    {
        $form = $this->createForm(EditEntryType::class, $entry);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($entry);
            $this->entityManager->flush();

            $this->addFlash(
                'notice',
                'flashes.entry.notice.entry_updated'
            );

            return $this->redirect($this->generateUrl('view', ['id' => $entry->getId()]));
        }

        return $this->render('Entry/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Shows all entries for current user.
     *
     * @param int $page
     *
     * @return Response
     */
    #[Route(path: '/all/list/{page}', name: 'all', methods: ['GET'], defaults: ['page' => '1'])]
    #[IsGranted('LIST_ENTRIES')]
    public function showAllAction(Request $request, $page)
    {
        return $this->showEntries('all', $request, $page);
    }

    /**
     * Shows unread entries for current user.
     *
     * @param int $page
     *
     * @return Response
     */
    #[Route(path: '/unread/list/{page}', name: 'unread', methods: ['GET'], defaults: ['page' => '1'])]
    #[IsGranted('LIST_ENTRIES')]
    public function showUnreadAction(Request $request, $page)
    {
        // load the quickstart if no entry in database
        if (1 === (int) $page && 0 === $this->entryRepository->countAllEntriesByUser($this->getUser()->getId())) {
            return $this->redirect($this->generateUrl('quickstart'));
        }

        return $this->showEntries('unread', $request, $page);
    }

    /**
     * Shows read entries for current user.
     *
     * @param int $page
     *
     * @return Response
     */
    #[Route(path: '/archive/list/{page}', name: 'archive', methods: ['GET'], defaults: ['page' => '1'])]
    #[IsGranted('LIST_ENTRIES')]
    public function showArchiveAction(Request $request, $page)
    {
        return $this->showEntries('archive', $request, $page);
    }

    /**
     * Shows starred entries for current user.
     *
     * @param int $page
     *
     * @return Response
     */
    #[Route(path: '/starred/list/{page}', name: 'starred', methods: ['GET'], defaults: ['page' => '1'])]
    #[IsGranted('LIST_ENTRIES')]
    public function showStarredAction(Request $request, $page)
    {
        return $this->showEntries('starred', $request, $page);
    }

    /**
     * Shows untagged articles for current user.
     *
     * @param int $page
     *
     * @return Response
     */
    #[Route(path: '/untagged/list/{page}', name: 'untagged', methods: ['GET'], defaults: ['page' => '1'])]
    #[IsGranted('LIST_ENTRIES')]
    public function showUntaggedEntriesAction(Request $request, $page)
    {
        return $this->showEntries('untagged', $request, $page);
    }

    /**
     * Shows entries with annotations for current user.
     *
     * @param int $page
     *
     * @return Response
     */
    #[Route(path: '/annotated/list/{page}', name: 'annotated', methods: ['GET'], defaults: ['page' => '1'])]
    #[IsGranted('LIST_ENTRIES')]
    public function showWithAnnotationsEntriesAction(Request $request, $page)
    {
        return $this->showEntries('annotated', $request, $page);
    }

    /**
     * Shows random entry depending on the given type.
     *
     * @return RedirectResponse
     */
    #[Route(path: '/{type}/random', name: 'random_entry', methods: ['GET'], requirements: ['type' => 'unread|starred|archive|untagged|annotated|all'])]
    #[IsGranted('LIST_ENTRIES')]
    public function redirectRandomEntryAction(string $type = 'all')
    {
        try {
            $entry = $this->entryRepository
                ->getRandomEntry($this->getUser()->getId(), $type);
        } catch (NoResultException) {
            $this->addFlash('notice', 'flashes.entry.notice.no_random_entry');

            return $this->redirect($this->generateUrl($type));
        }

        return $this->redirect($this->generateUrl('view', ['id' => $entry->getId()]));
    }

    /**
     * Shows entry content.
     *
     * @return Response
     */
    #[Route(path: '/view/{id}', name: 'view', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[IsGranted('VIEW', subject: 'entry')]
    public function viewAction(Entry $entry)
    {
        return $this->render(
            'Entry/entry.html.twig',
            ['entry' => $entry]
        );
    }

    /**
     * Reload an entry.
     * Refetch content from the website and make it readable again.
     *
     * @return RedirectResponse
     */
    #[Route(path: '/reload/{id}', name: 'reload_entry', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('RELOAD', subject: 'entry')]
    public function reloadAction(Request $request, Entry $entry)
    {
        if (!$this->isCsrfTokenValid('reload-entry', $request->request->get('token'))) {
            throw new BadRequestHttpException('Bad CSRF token.');
        }

        $this->updateEntry($entry, 'entry_reloaded');

        // if refreshing entry failed, don't save it
        if ($this->getParameter('wallabag.fetching_error_message') === $entry->getContent()) {
            $this->addFlash('notice', 'flashes.entry.notice.entry_reloaded_failed');

            return $this->redirect($this->generateUrl('view', ['id' => $entry->getId()]));
        }

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        // entry saved, dispatch event about it!
        $this->eventDispatcher->dispatch(new EntrySavedEvent($entry), EntrySavedEvent::NAME);

        return $this->redirect($this->generateUrl('view', ['id' => $entry->getId()]));
    }

    /**
     * Changes read status for an entry.
     *
     * @return RedirectResponse
     */
    #[Route(path: '/archive/{id}', name: 'archive_entry', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ARCHIVE', subject: 'entry')]
    public function toggleArchiveAction(Request $request, Entry $entry)
    {
        if (!$this->isCsrfTokenValid('archive-entry', $request->request->get('token'))) {
            throw new BadRequestHttpException('Bad CSRF token.');
        }

        $entry->toggleArchive();
        $this->entityManager->flush();

        $message = 'flashes.entry.notice.entry_unarchived';
        if ($entry->isArchived()) {
            $message = 'flashes.entry.notice.entry_archived';
        }

        $this->addFlash(
            'notice',
            $message
        );

        $redirectUrl = $this->redirectHelper->to($request->query->get('redirect'));

        return $this->redirect($redirectUrl);
    }

    /**
     * Changes starred status for an entry.
     *
     * @return RedirectResponse
     */
    #[Route(path: '/star/{id}', name: 'star_entry', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('STAR', subject: 'entry')]
    public function toggleStarAction(Request $request, Entry $entry)
    {
        if (!$this->isCsrfTokenValid('star-entry', $request->request->get('token'))) {
            throw new BadRequestHttpException('Bad CSRF token.');
        }

        $entry->toggleStar();
        $entry->updateStar($entry->isStarred());
        $this->entityManager->flush();

        $message = 'flashes.entry.notice.entry_unstarred';
        if ($entry->isStarred()) {
            $message = 'flashes.entry.notice.entry_starred';
        }

        $this->addFlash(
            'notice',
            $message
        );

        $redirectUrl = $this->redirectHelper->to($request->query->get('redirect'));

        return $this->redirect($redirectUrl);
    }

    /**
     * Deletes entry and redirect to the homepage or the last viewed page.
     *
     * @return RedirectResponse
     */
    #[Route(path: '/delete/{id}', name: 'delete_entry', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('DELETE', subject: 'entry')]
    public function deleteEntryAction(Request $request, Entry $entry)
    {
        if (!$this->isCsrfTokenValid('delete-entry', $request->request->get('token'))) {
            throw new BadRequestHttpException('Bad CSRF token.');
        }

        // generates the view url for this entry to check for redirection later
        // to avoid redirecting to the deleted entry. Ugh.
        $url = $this->generateUrl(
            'view',
            ['id' => $entry->getId()]
        );

        // entry deleted, dispatch event about it!
        $this->eventDispatcher->dispatch(new EntryDeletedEvent($entry), EntryDeletedEvent::NAME);

        $this->entityManager->remove($entry);
        $this->entityManager->flush();

        $this->addFlash(
            'notice',
            'flashes.entry.notice.entry_deleted'
        );

        // don't redirect user to the deleted entry (check that the referer doesn't end with the same url)
        $prev = $request->query->get('redirect', '');
        $to = (1 !== preg_match('#' . $url . '$#i', $prev) ? $prev : null);

        $redirectUrl = $this->redirectHelper->to($to);

        return $this->redirect($redirectUrl);
    }

    /**
     * Get public URL for entry (and generate it if necessary).
     *
     * @return Response
     */
    #[Route(path: '/share/{id}', name: 'share', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('SHARE', subject: 'entry')]
    public function shareAction(Request $request, Entry $entry)
    {
        if (!$this->isCsrfTokenValid('share-entry', $request->request->get('token'))) {
            throw new BadRequestHttpException('Bad CSRF token.');
        }

        if (null === $entry->getUid()) {
            $entry->generateUid();

            $this->entityManager->persist($entry);
            $this->entityManager->flush();
        }

        return $this->redirect($this->generateUrl('share_entry', [
            'uid' => $entry->getUid(),
        ]));
    }

    /**
     * Disable public sharing for an entry.
     *
     * @return Response
     */
    #[Route(path: '/share/delete/{id}', name: 'delete_share', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('UNSHARE', subject: 'entry')]
    public function deleteShareAction(Request $request, Entry $entry)
    {
        if (!$this->isCsrfTokenValid('delete-share', $request->request->get('token'))) {
            throw new BadRequestHttpException('Bad CSRF token.');
        }

        $entry->cleanUid();

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        return $this->redirect($this->generateUrl('view', [
            'id' => $entry->getId(),
        ]));
    }

    /**
     * Ability to view a content publicly.
     *
     * @return Response
     */
    #[Route(path: '/share/{uid}', name: 'share_entry', methods: ['GET'], requirements: ['uid' => '.+'])]
    #[Cache(maxage: 25200, smaxage: 25200, public: true)]
    #[IsGranted('PUBLIC_ACCESS')]
    public function shareEntryAction(Entry $entry, Config $craueConfig)
    {
        if (!$craueConfig->get('share_public')) {
            throw $this->createAccessDeniedException('Sharing an entry is disabled for this user.');
        }

        return $this->render(
            'Entry/share.html.twig',
            ['entry' => $entry]
        );
    }

    /**
     * List the entries with the same domain as the current one.
     *
     * @param int $page
     *
     * @return Response
     */
    #[Route(path: '/domain/{id}/{page}', name: 'same_domain', methods: ['GET'], requirements: ['id' => '\d+'], defaults: ['page' => 1])]
    #[IsGranted('LIST_ENTRIES')]
    public function getSameDomainEntries(Request $request, $page = 1)
    {
        return $this->showEntries('same-domain', $request, $page);
    }

    /**
     * Global method to retrieve entries depending on the given type
     * It returns the response to be send.
     *
     * @param string $type Entries type: unread, starred or archive
     * @param int    $page
     *
     * @return Response
     */
    private function showEntries($type, Request $request, $page)
    {
        $searchTerm = (isset($request->query->all('search_entry')['term']) ? trim((string) $request->query->all('search_entry')['term']) : '');
        $currentRoute = $request->query->get('currentRoute') ?? '';
        $currentEntryId = $request->attributes->getInt('id');

        $formOptions = [];

        switch ($type) {
            case 'search':
                $qb = $this->entryRepository->getBuilderForSearchByUser($this->getUser()->getId(), $searchTerm, $currentRoute);
                break;
            case 'untagged':
                $qb = $this->entryRepository->getBuilderForUntaggedByUser($this->getUser()->getId());
                break;
            case 'starred':
                $qb = $this->entryRepository->getBuilderForStarredByUser($this->getUser()->getId());
                $formOptions['filter_starred'] = true;
                break;
            case 'archive':
                $qb = $this->entryRepository->getBuilderForArchiveByUser($this->getUser()->getId());
                $formOptions['filter_archived'] = true;
                break;
            case 'annotated':
                $qb = $this->entryRepository->getBuilderForAnnotationsByUser($this->getUser()->getId());
                break;
            case 'unread':
                $qb = $this->entryRepository->getBuilderForUnreadByUser($this->getUser()->getId());
                $formOptions['filter_unread'] = true;
                break;
            case 'same-domain':
                $qb = $this->entryRepository->getBuilderForSameDomainByUser($this->getUser()->getId(), $currentEntryId);
                break;
            case 'all':
                $qb = $this->entryRepository->getBuilderForAllByUser($this->getUser()->getId());
                break;
            default:
                throw new \InvalidArgumentException(\sprintf('Type "%s" is not implemented.', $type));
        }

        $form = $this->createForm(EntryFilterType::class, [], $formOptions);

        if ($request->query->has($form->getName())) {
            // manually bind values from the request
            $form->submit($request->query->all($form->getName()));

            // build the query from the given form object
            $this->filterBuilderUpdater->addFilterConditions($form, $qb);
        }

        $pagerAdapter = new DoctrineORMAdapter($qb->getQuery(), true, false);

        $entries = $this->preparePagerForEntriesHelper->prepare($pagerAdapter);

        try {
            $entries->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException) {
            if ($page > 1) {
                return $this->redirect($this->generateUrl($type, ['page' => $entries->getNbPages()]), 302);
            }
        }

        return $this->render(
            'Entry/entries.html.twig', [
                'form' => $form->createView(),
                'entries' => $entries,
                'currentPage' => $page,
                'searchTerm' => $searchTerm,
                'isFiltered' => $form->isSubmitted(),
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
            $this->contentProxy->updateEntry($entry, $entry->getUrl());
        } catch (\Exception) {
            // $this->logger->error('Error while saving an entry', [
            //     'exception' => $e,
            //     'entry' => $entry,
            // ]);

            $message = 'flashes.entry.notice.' . $prefixMessage . '_failed';
        }

        if (empty($entry->getDomainName())) {
            $this->contentProxy->setEntryDomainName($entry);
        }

        if (empty($entry->getTitle())) {
            $this->contentProxy->setDefaultEntryTitle($entry);
        }

        $this->addFlash('notice', $message);
    }

    /**
     * Check for existing entry, if it exists, redirect to it with a message.
     *
     * @return Entry|bool
     */
    private function checkIfEntryAlreadyExists(Entry $entry)
    {
        return $this->entryRepository->findByUrlAndUserId($entry->getUrl(), $this->getUser()->getId());
    }
}
