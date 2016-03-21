<?php

namespace Wallabag\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Form\Type\NewTagType;

class TagController extends Controller
{
    /**
     * @param Request $request
     *
     * @Route("/new-tag/{entry}", requirements={"entry" = "\d+"}, name="new_tag")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addTagFormAction(Request $request, Entry $entry)
    {
        $tag = new Tag();
        $form = $this->createForm(NewTagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $existingTag = $this->getDoctrine()
                ->getRepository('WallabagCoreBundle:Tag')
                ->findOneByLabel($tag->getLabel());

            $em = $this->getDoctrine()->getManager();

            if (is_null($existingTag)) {
                $entry->addTag($tag);
                $em->persist($tag);
            } elseif (!$existingTag->hasEntry($entry)) {
                $entry->addTag($existingTag);
                $em->persist($existingTag);
            }

            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'flashes.tag.notice.tag_added'
            );

            return $this->redirect($this->generateUrl('view', array('id' => $entry->getId())));
        }

        return $this->render('WallabagCoreBundle:Tag:new_form.html.twig', array(
            'form' => $form->createView(),
            'entry' => $entry,
        ));
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
        if (count($tag->getEntries()) == 0) {
            $em->remove($tag);
        }
        $em->flush();

        return $this->redirect($request->headers->get('referer'));
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
            ->findTags($this->getUser()->getId());

        return $this->render(
            'WallabagCoreBundle:Tag:tags.html.twig',
            array(
                'tags' => $tags,
            )
        );
    }
}
