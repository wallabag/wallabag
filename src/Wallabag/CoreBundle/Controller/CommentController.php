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
     * @Route("/comment/new/{entry}", requirements={"entry" = "\d+"}, name="new_comment")
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

    /**
     * Deletes comment.
     *
     * @param Comment $comment
     * @param Request $request
     *
     * @Route("/comment/delete/{id}", requirements={"id" = "\d+"}, name="delete_comment")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteCommentAction(Request $request, Comment $comment)
    {
        $this->checkUserAction($comment);

        if (!$comment) {
            throw $this->createNotFoundException('No comment found');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Comment deleted'
        );

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Check if the logged user can manage the given comment.
     *
     * @param Comment $comment
     */
    private function checkUserAction(Comment $comment)
    {
        if ($this->getUser()->getId() != $comment->getUser()->getId()) {
            throw $this->createAccessDeniedException('You can not access this entry.');
        }
    }
}
