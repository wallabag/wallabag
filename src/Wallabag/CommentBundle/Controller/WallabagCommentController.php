<?php

namespace Wallabag\CommentBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Wallabag\CommentBundle\Entity\Comment;
use Wallabag\CoreBundle\Entity\Entry;

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
        $commentRows = $this
                ->getDoctrine()
                ->getRepository('WallabagCommentBundle:Comment')
                ->findCommentsByPageId($entry->getId(), $this->getUser()->getId());
        $total = count($commentRows);
        $comments = array('total' => $total, 'rows' => $commentRows);

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

        $em = $this->getDoctrine()->getManager();

        $comment = new Comment($this->getUser());

        $comment->setText($data['text']);
        if (array_key_exists('quote', $data)) {
            $comment->setQuote($data['quote']);
        }
        if (array_key_exists('ranges', $data)) {
            $comment->setRanges($data['ranges']);
        }

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
     * @ParamConverter("comment", class="WallabagCommentBundle:Comment")
     *
     * @return Response
     */
    public function putAnnotationAction(Comment $comment, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (!is_null($data['text'])) {
            $comment->setText($data['text']);
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
     * @ParamConverter("comment", class="WallabagCommentBundle:Comment")
     *
     * @return Response
     */
    public function deleteAnnotationAction(Comment $comment)
    {
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
}
