<?php

namespace Wallabag\CoreBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Form\Type\NewTagType;
use Wallabag\CoreBundle\Form\Type\RenameTagType;

class TagController extends Controller
{
    /**
     * @Route("/new-tag/{entry}", requirements={"entry" = "\d+"}, name="new_tag")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addTagFormAction(Request $request, Entry $entry)
    {
        $form = $this->createForm(NewTagType::class, new Tag());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('wallabag_core.tags_assigner')->assignTagsToEntry(
                $entry,
                $form->get('label')->getData()
            );

            $em = $this->get('doctrine')->getManager();
            $em->persist($entry);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'flashes.tag.notice.tag_added'
            );

            return $this->redirect($this->generateUrl('view', ['id' => $entry->getId()]));
        }

        return $this->render('WallabagCoreBundle:Tag:new_form.html.twig', [
            'form' => $form->createView(),
            'entry' => $entry,
        ]);
    }

    /**
     * Removes tag from entry.
     *
     * @Route("/remove-tag/{entry}/{tag}", requirements={"entry" = "\d+", "tag" = "\d+"}, name="remove_tag")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeTagFromEntry(Request $request, Entry $entry, Tag $tag)
    {
        $entry->removeTag($tag);
        $em = $this->get('doctrine')->getManager();
        $em->flush();

        // remove orphan tag in case no entries are associated to it
        if (0 === \count($tag->getEntries())) {
            $em->remove($tag);
            $em->flush();
        }

        $redirectUrl = $this->get('wallabag_core.helper.redirect')->to($request->headers->get('referer'), '', true);

        return $this->redirect($redirectUrl);
    }

    /**
     * Shows tags for current user.
     *
     * @Route("/tag/list", name="tag")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showTagAction()
    {
        $tags = $this->get('wallabag_core.tag_repository')
            ->findAllFlatTagsWithNbEntries($this->getUser()->getId());
        $nbEntriesUntagged = $this->get('wallabag_core.entry_repository')
            ->countUntaggedEntriesByUser($this->getUser()->getId());

        $renameForms = [];
        foreach ($tags as $tag) {
            $renameForms[$tag['id']] = $this->createForm(RenameTagType::class, new Tag())->createView();
        }

        return $this->render('WallabagCoreBundle:Tag:tags.html.twig', [
            'tags' => $tags,
            'renameForms' => $renameForms,
            'nbEntriesUntagged' => $nbEntriesUntagged,
        ]);
    }

    /**
     * @param int $page
     *
     * @Route("/tag/list/{slug}/{page}", name="tag_entries", defaults={"page" = "1"})
     * @ParamConverter("tag", options={"mapping": {"slug": "slug"}})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showEntriesForTagAction(Tag $tag, $page, Request $request)
    {
        $entriesByTag = $this->get('wallabag_core.entry_repository')->findAllByTagId(
            $this->getUser()->getId(),
            $tag->getId()
        );

        $pagerAdapter = new ArrayAdapter($entriesByTag);

        $entries = $this->get('wallabag_core.helper.prepare_pager_for_entries')->prepare($pagerAdapter);

        try {
            $entries->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException $e) {
            if ($page > 1) {
                return $this->redirect($this->generateUrl($request->get('_route'), [
                    'slug' => $tag->getSlug(),
                    'page' => $entries->getNbPages(),
                ]), 302);
            }
        }

        return $this->render('WallabagCoreBundle:Entry:entries.html.twig', [
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
     * @Route("/tag/rename/{slug}", name="tag_rename")
     * @ParamConverter("tag", options={"mapping": {"slug": "slug"}})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renameTagAction(Tag $tag, Request $request)
    {
        $form = $this->createForm(RenameTagType::class, new Tag());
        $form->handleRequest($request);

        $redirectUrl = $this->get('wallabag_core.helper.redirect')->to($request->headers->get('referer'), '', true);

        if ($form->isSubmitted() && $form->isValid()) {
            $newTag = new Tag();
            $newTag->setLabel($form->get('label')->getData());

            if ($newTag->getLabel() === $tag->getLabel()) {
                return $this->redirect($redirectUrl);
            }

            $tagFromRepo = $this->get('wallabag_core.tag_repository')->findOneByLabel($newTag->getLabel());

            if (null !== $tagFromRepo) {
                $newTag = $tagFromRepo;
            }

            $entries = $this->get('wallabag_core.entry_repository')->findAllByTagId(
                $this->getUser()->getId(),
                $tag->getId()
            );
            foreach ($entries as $entry) {
                $this->get('wallabag_core.tags_assigner')->assignTagsToEntry(
                    $entry,
                    $newTag->getLabel(),
                    [$newTag]
                );
                $entry->removeTag($tag);
            }

            $this->get('doctrine')->getManager()->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'flashes.tag.notice.tag_renamed'
            );
        }

        return $this->redirect($redirectUrl);
    }

    /**
     * Tag search results with the current search term.
     *
     * @Route("/tag/search/{filter}", name="tag_this_search")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function tagThisSearchAction($filter, Request $request)
    {
        $currentRoute = $request->query->has('currentRoute') ? $request->query->get('currentRoute') : '';

        /** @var QueryBuilder $qb */
        $qb = $this->get('wallabag_core.entry_repository')->getBuilderForSearchByUser($this->getUser()->getId(), $filter, $currentRoute);
        $em = $this->get('doctrine')->getManager();

        $entries = $qb->getQuery()->getResult();

        foreach ($entries as $entry) {
            $this->get('wallabag_core.tags_assigner')->assignTagsToEntry(
                $entry,
                $filter
            );

            $em->persist($entry);
        }

        $em->flush();

        return $this->redirect($this->get('wallabag_core.helper.redirect')->to($request->headers->get('referer'), '', true));
    }

    /**
     * Delete a given tag for the current user.
     *
     * @Route("/tag/delete/{slug}", name="tag_delete")
     * @ParamConverter("tag", options={"mapping": {"slug": "slug"}})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeTagAction(Tag $tag, Request $request)
    {
        foreach ($tag->getEntriesByUserId($this->getUser()->getId()) as $entry) {
            $this->get('wallabag_core.entry_repository')->removeTag($this->getUser()->getId(), $tag);
        }

        // remove orphan tag in case no entries are associated to it
        if (0 === \count($tag->getEntries())) {
            $em = $this->get('doctrine')->getManager();
            $em->remove($tag);
            $em->flush();
        }

        $redirectUrl = $this->get('wallabag_core.helper.redirect')->to($request->headers->get('referer'), '', true);

        return $this->redirect($redirectUrl);
    }
}
