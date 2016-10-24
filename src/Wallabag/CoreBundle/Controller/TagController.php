<?php

namespace Wallabag\CoreBundle\Controller;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Form\Type\NewTagType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class TagController extends Controller
{
    /**
     * @param Request $request
     * @param Entry   $entry
     *
     * @Route("/new-tag/{entry}", requirements={"entry" = "\d+"}, name="new_tag")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addTagFormAction(Request $request, Entry $entry)
    {
        $form = $this->createForm(NewTagType::class, new Tag());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('wallabag_core.content_proxy')->assignTagsToEntry(
                $entry,
                $form->get('label')->getData()
            );

            $em = $this->getDoctrine()->getManager();
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
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        // remove orphan tag in case no entries are associated to it
        if (count($tag->getEntries()) === 0) {
            $em->remove($tag);
            $em->flush();
        }

        $redirectUrl = $this->get('wallabag_core.helper.redirect')->to($request->headers->get('referer'));

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
        $tags = $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Tag')
            ->findAllTags($this->getUser()->getId());

        $flatTags = [];

        foreach ($tags as $tag) {
            $nbEntries = $this->getDoctrine()
                ->getRepository('WallabagCoreBundle:Entry')
                ->countAllEntriesByUserIdAndTagId($this->getUser()->getId(), $tag->getId());

            $flatTags[] = [
                'id' => $tag->getId(),
                'label' => $tag->getLabel(),
                'slug' => $tag->getSlug(),
                'nbEntries' => $nbEntries,
            ];
        }

        return $this->render('WallabagCoreBundle:Tag:tags.html.twig', [
            'tags' => $flatTags,
        ]);
    }

    /**
     * @param Tag $tag
     * @param int $page
     *
     * @Route("/tag/list/{slug}/{page}", name="tag_entries", defaults={"page" = "1"})
     * @ParamConverter("tag", options={"mapping": {"slug": "slug"}})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showEntriesForTagAction(Tag $tag, $page, Request $request)
    {
        $entriesByTag = $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entry')
            ->findAllByTagId($this->getUser()->getId(), $tag->getId());

        $pagerAdapter = new ArrayAdapter($entriesByTag);

        $entries = $this->get('wallabag_core.helper.prepare_pager_for_entries')
            ->prepare($pagerAdapter, $page);

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
        ]);
    }
}
