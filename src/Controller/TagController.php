<?php

namespace Wallabag\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wallabag\Entity\Entry;
use Wallabag\Entity\Tag;
use Wallabag\Form\Type\NewTagType;
use Wallabag\Form\Type\RenameTagType;
use Wallabag\Helper\PreparePagerForEntries;
use Wallabag\Helper\Redirect;
use Wallabag\Helper\TagsAssigner;
use Wallabag\Repository\EntryRepository;
use Wallabag\Repository\TagRepository;

class TagController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TagsAssigner $tagsAssigner,
        private Redirect $redirectHelper,
        private Security $security,
    ) {
    }

    /**
     * @Route("/new-tag/{entry}", name="new_tag", methods={"POST"}, requirements={"entry" = "\d+"})
     * @IsGranted("TAG", subject="entry")
     *
     * @return Response
     */
    public function addTagFormAction(Request $request, Entry $entry, TranslatorInterface $translator)
    {
        $form = $this->createForm(NewTagType::class, new Tag());
        $form->handleRequest($request);

        $tags = $form->get('label')->getData() ?? '';
        $tagsExploded = explode(',', $tags);

        // avoid too much tag to be added
        if (\count($tagsExploded) >= NewTagType::MAX_TAGS || \strlen($tags) >= NewTagType::MAX_LENGTH) {
            $message = $translator->trans('flashes.tag.notice.too_much_tags', [
                '%tags%' => NewTagType::MAX_TAGS,
                '%characters%' => NewTagType::MAX_LENGTH,
            ]);
            $this->addFlash('notice', $message);

            return $this->redirect($this->generateUrl('view', ['id' => $entry->getId()]));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->tagsAssigner->assignTagsToEntry(
                $entry,
                $form->get('label')->getData()
            );

            $this->entityManager->persist($entry);
            $this->entityManager->flush();

            $this->addFlash(
                'notice',
                'flashes.tag.notice.tag_added'
            );

            return $this->redirect($this->generateUrl('view', ['id' => $entry->getId()]));
        }

        return $this->render('Tag/new_form.html.twig', [
            'form' => $form->createView(),
            'entry' => $entry,
        ]);
    }

    /**
     * Removes tag from entry.
     *
     * @Route("/remove-tag/{entry}/{tag}", name="remove_tag", methods={"GET"}, requirements={"entry" = "\d+", "tag" = "\d+"})
     * @IsGranted("UNTAG", subject="entry")
     *
     * @return Response
     */
    public function removeTagFromEntry(Request $request, Entry $entry, Tag $tag)
    {
        $entry->removeTag($tag);
        $this->entityManager->flush();

        // remove orphan tag in case no entries are associated to it
        if (0 === \count($tag->getEntries()) && $this->security->isGranted('DELETE', $tag)) {
            $this->entityManager->remove($tag);
            $this->entityManager->flush();
        }

        $redirectUrl = $this->redirectHelper->to($request->query->get('redirect'), true);

        return $this->redirect($redirectUrl);
    }

    /**
     * Shows tags for current user.
     *
     * @Route("/tag/list", name="tag", methods={"GET"})
     * @IsGranted("LIST_TAGS")
     *
     * @return Response
     */
    public function showTagAction(TagRepository $tagRepository, EntryRepository $entryRepository)
    {
        $allTagsWithNbEntries = $tagRepository->findAllTagsWithNbEntries($this->getUser()->getId());
        $nbEntriesUntagged = $entryRepository->countUntaggedEntriesByUser($this->getUser()->getId());

        $renameForms = [];
        foreach ($allTagsWithNbEntries as $tagWithNbEntries) {
            $renameForms[$tagWithNbEntries['tag']->getId()] = $this->createForm(RenameTagType::class, new Tag())->createView();
        }

        return $this->render('Tag/tags.html.twig', [
            'allTagsWithNbEntries' => $allTagsWithNbEntries,
            'renameForms' => $renameForms,
            'nbEntriesUntagged' => $nbEntriesUntagged,
        ]);
    }

    /**
     * @param int $page
     *
     * @Route("/tag/list/{slug}/{page}", name="tag_entries", methods={"GET"}, defaults={"page" = "1"})
     * @ParamConverter("tag", options={"mapping": {"slug": "slug"}})
     * @IsGranted("LIST_ENTRIES")
     * @IsGranted("VIEW", subject="tag")
     *
     * @return Response
     */
    public function showEntriesForTagAction(Tag $tag, EntryRepository $entryRepository, PreparePagerForEntries $preparePagerForEntries, $page, Request $request)
    {
        $entriesByTag = $entryRepository->findAllByTagId(
            $this->getUser()->getId(),
            $tag->getId()
        );

        $pagerAdapter = new ArrayAdapter($entriesByTag);

        $entries = $preparePagerForEntries->prepare($pagerAdapter);

        try {
            $entries->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException) {
            if ($page > 1) {
                return $this->redirect($this->generateUrl($request->attributes->get('_route'), [
                    'slug' => $tag->getSlug(),
                    'page' => $entries->getNbPages(),
                ]), 302);
            }
        }

        return $this->render('Entry/entries.html.twig', [
            'form' => null,
            'entries' => $entries,
            'currentPage' => $page,
            'tag' => $tag,
        ]);
    }

    /**
     * Rename a given tag with a new label
     * Create a new tag with the new name and drop the old one.
     *
     * @Route("/tag/rename/{slug}", name="tag_rename", methods={"POST"})
     * @ParamConverter("tag", options={"mapping": {"slug": "slug"}})
     * @IsGranted("EDIT", subject="tag")
     *
     * @return Response
     */
    public function renameTagAction(Tag $tag, Request $request, TagRepository $tagRepository, EntryRepository $entryRepository)
    {
        $form = $this->createForm(RenameTagType::class, new Tag());
        $form->handleRequest($request);

        $redirectUrl = $this->redirectHelper->to($request->query->get('redirect'), true);

        if ($form->isSubmitted() && $form->isValid()) {
            $newTag = new Tag();
            $newTag->setLabel($form->get('label')->getData());

            if ($newTag->getLabel() === $tag->getLabel()) {
                return $this->redirect($redirectUrl);
            }

            $tagFromRepo = $tagRepository->findOneByLabel($newTag->getLabel());

            if (null !== $tagFromRepo) {
                $newTag = $tagFromRepo;
            }

            $entries = $entryRepository->findAllByTagId(
                $this->getUser()->getId(),
                $tag->getId()
            );
            foreach ($entries as $entry) {
                $this->tagsAssigner->assignTagsToEntry(
                    $entry,
                    $newTag->getLabel(),
                    [$newTag]
                );
                $entry->removeTag($tag);
            }

            $this->entityManager->flush();

            $this->addFlash(
                'notice',
                'flashes.tag.notice.tag_renamed'
            );
        }

        return $this->redirect($redirectUrl);
    }

    /**
     * Tag search results with the current search term.
     *
     * @Route("/tag/search/{filter}", name="tag_this_search", methods={"GET"})
     * @IsGranted("CREATE_TAGS")
     *
     * @return Response
     */
    public function tagThisSearchAction($filter, Request $request, EntryRepository $entryRepository)
    {
        $currentRoute = $request->query->has('currentRoute') ? $request->query->get('currentRoute') : '';

        /** @var QueryBuilder $qb */
        $qb = $entryRepository->getBuilderForSearchByUser($this->getUser()->getId(), $filter, $currentRoute);

        $entries = $qb->getQuery()->getResult();

        foreach ($entries as $entry) {
            $this->tagsAssigner->assignTagsToEntry(
                $entry,
                $filter
            );

            // check to avoid duplicate tags creation
            foreach ($this->entityManager->getUnitOfWork()->getScheduledEntityInsertions() as $entity) {
                if ($entity instanceof Tag && strtolower($entity->getLabel()) === strtolower($filter)) {
                    continue 2;
                }
                $this->entityManager->persist($entry);
            }
            $this->entityManager->flush();
        }

        return $this->redirect($this->redirectHelper->to($request->query->get('redirect'), true));
    }

    /**
     * Delete a given tag for the current user.
     *
     * @Route("/tag/delete/{slug}", name="tag_delete", methods={"GET"})
     * @ParamConverter("tag", options={"mapping": {"slug": "slug"}})
     * @IsGranted("DELETE", subject="tag")
     *
     * @return Response
     */
    public function removeTagAction(Tag $tag, Request $request, EntryRepository $entryRepository)
    {
        foreach ($tag->getEntriesByUserId($this->getUser()->getId()) as $entry) {
            $entryRepository->removeTag($this->getUser()->getId(), $tag);
        }

        // remove orphan tag in case no entries are associated to it
        if (0 === \count($tag->getEntries())) {
            $this->entityManager->remove($tag);
            $this->entityManager->flush();
        }
        $redirectUrl = $this->redirectHelper->to($request->query->get('redirect'), true);

        return $this->redirect($redirectUrl);
    }
}
