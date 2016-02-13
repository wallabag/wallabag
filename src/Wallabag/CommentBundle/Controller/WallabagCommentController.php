<?php

namespace Wallabag\CommentBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wallabag\CommentBundle\Entity\Comment;
use Wallabag\CoreBundle\Entity\Entry;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class WallabagCommentController extends FOSRestController
{
    /**
     * Retrieve comments for an entry.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     *
     * @return Response
     */
    public function getAnnotationsAction(Entry $entry)
    {
        $commentrows = $this
                ->getDoctrine()
                ->getRepository('WallabagCommentBundle:Comment')
                ->findCommentsByPageId($entry->getId(), $this->getUser()->getId());
        $total = count($commentrows);
        $comments = array('total' => $total, 'rows' => $commentrows);
        $this->validateAuthentication();

        $json = $this->get('serializer')->serialize($comments, 'json');

        return $this->renderJsonResponse($json);
    }

    /**
     * Creates a new comment.
     *
     * @param Entry $entry
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="ranges", "dataType"="array", "requirement"="\w+", "description"="The range array for the annotation"},
     *          {"name"="quote", "dataType"="string", "required"=false, "description"="Optional, quote for the comment"},
     *          {"name"="text", "dataType"="string", "required"=true, "description"=""},
     *      }
     * )
     *
     * @return Response
     */
    public function postAnnotationAction(Request $request, Entry $entry)
    {
        $data = json_decode($request->getContent(), true);

        $this->validateAuthentication();

        $em = $this->getDoctrine()->getManager();

        $comment = new Comment($this->getUser());

        $comment->setText($data['text']);
        if (array_key_exists('quote', $data)) {
            $comment->setQuote($data['quote']);
        }
        if (array_key_exists('ranges', $data)) {
            $comment->setRanges($data['ranges']);
        }
        $comment->setUpdated(new \DateTime());

        $comment->setEntry($entry);

        $em->persist($comment);
        $em->flush();

        $json = $this->get('serializer')->serialize($comment, 'json');

        return $this->renderJsonResponse($json);
    }

    /**
     * Updates a comment.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="comment", "dataType"="string", "requirement"="\w+", "description"="The comment ID"}
     *      }
     * )
     *
     * @return Response
     */
    public function putAnnotationAction($idcomment, Request $request)
    {
        $this->validateAuthentication();

        $data = json_decode($request->getContent(), true);

        $comment = $this
                ->getDoctrine()
                ->getRepository('WallabagCommentBundle:Comment')
                ->findCommentById($idcomment);

        if (!is_null($data['text'])) {
            $comment->setText($data['text']);
            $comment->setUpdated(new \DateTime());
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $json = $this->get('serializer')->serialize($comment, 'json');

        return $this->renderJsonResponse($json);
    }

    /**
     * Removes a comment.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="comment", "dataType"="string", "requirement"="\w+", "description"="The comment ID"}
     *      }
     * )
     *
     * @return Response
     */
    public function deleteAnnotationAction($idcomment)
    {
        $this->validateAuthentication();

        $comment = $this
                ->getDoctrine()
                ->getRepository('WallabagCommentBundle:Comment')
                ->findCommentById($idcomment);

        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();

        $json = $this->get('serializer')->serialize($comment, 'json');

        return $this->renderJsonResponse($json);
    }

    /**
     * Send a JSON Response.
     * We don't use the Symfony JsonRespone, because it takes an array as parameter instead of a JSON string.
     *
     * @param string $json
     *
     * @return Response
     */
    private function renderJsonResponse($json, $code = 200)
    {
        return new Response($json, $code, array('application/json'));
    }

    private function validateAuthentication()
    {
        if (false === $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }
    }
}
