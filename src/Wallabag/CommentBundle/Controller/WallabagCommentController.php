<?php

namespace Wallabag\CommentBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Hateoas\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wallabag\CommentBundle\Entity\Comment;
use Wallabag\CoreBundle\Entity\Entry;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class WallabagCommentController extends FOSRestController
{

    /**
     * Retrieve all comments for the user
     *
     * @ApiDoc()
     *
     *
     * @return Response
     */
    // public function getAnnotationsAction()
    // {
    //     $comments = $this
    //             ->getDoctrine()
    //             ->getRepository('WallabagCommentBundle:Comment')
    //             ->getBuilderForAllByUser($this->getUser())
    //             ->getQuery()->getResult();

    //     $this->validateAuthentication();

    //     $json = $this->get('serializer')->serialize($comments, 'json');

    //     return $this->renderJsonResponse($json);
    // }


    /**
     * Retrieve comments for an entry
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="page", "dataType"="integer", "requirement"="\w+", "description"="The page ID"}
     *      }
     * )
     *
     * @return Response
     */
    public function getAnnotationsAction(Request $request, Entry $entry)
    {
        $commentrows = $this
                ->getDoctrine()
                ->getRepository('WallabagCommentBundle:Comment')
                ->findCommentsByPageId($entry->getId());
        $total = count($commentrows);
        $comments = array('total' => $total, 'rows' => $commentrows);
        // $logger = $this->get('logger');
        // $logger->error('hello');
        $this->validateAuthentication();

        $json = $this->get('serializer')->serialize($comments, 'json');

        return $this->renderJsonResponse($json);
    }

    /**
     * Creates a new comment.
     *
     * @param Entry   $entry
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

        // $logger = $this->get('logger');
        // $logger->error(print_r($data));

        $em = $this->getDoctrine()->getManager();

        $comment = new Comment($this->getUser());

        $comment->setText($data['text']);
        if (array_key_exists('quote',$data)) {
            $comment->setQuote($data['quote']);
        }
        if (array_key_exists('ranges',$data)) {
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
     *          {"name"="entry", "dataType"="string", "requirement"="\w+", "description"="The comment ID"}
     *      }
     * )
     *
     * @return Response
     */
    public function patchAnnotationAction(Comment $comment, Request $request)
    {
        return $this->render('WallabagCommentBundle:CommentController:edit_comment.html.php', array(
            // ...
        ));
    }

    public function deleteAnnotationAction($idcomment) {
        //
    }

    /**
     * Send a JSON Response.
     * We don't use the Symfony JsonRespone, because it takes an array as parameter instead of a JSON string.
     *
     * @param string $json
     *
     * @return Response
     */
    private function renderJsonResponse($json,$code = 200)
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
