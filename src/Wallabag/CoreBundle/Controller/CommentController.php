<?php

namespace Wallabag\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\CoreBundle\Form\Type\NewCommentType;
use Wallabag\CoreBundle\Entity\Comment;
use Wallabag\CoreBundle\Entity\Entry;

class CommentController extends Controller
{
    /**
     * @param Request $request
     *
     * @Route("/new-comment/{entry}", requirements={"entry" = "\d+"}, name="new_comment")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addCommentFormAction(Request $request, Entry $entry)
    {
        $comment = new Comment($this->getUser());
        $form = $this->createForm(new NewCommentType(), $comment);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $entry->addComment($comment);
            $comment->setEntry($entry);

            $em->persist($comment);

            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Comment added'
            );

            return $this->redirect($this->generateUrl('view', array('id' => $entry->getId())));
        }

        return $this->render('WallabagCoreBundle:Entry:new_comment_form.html.twig', array(
            'form' => $form->createView(),
            'entry' => $entry,
        ));
    }
}
