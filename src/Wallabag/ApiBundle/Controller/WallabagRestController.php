<?php

namespace Wallabag\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Hateoas\Configuration\Route;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\AnnotationBundle\Entity\Annotation;

class WallabagRestController extends FOSRestController
{
    private function validateAuthentication()
    {
        if (false === $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }
    }

    /**
     * Retrieve all entries. It could be filtered by many options.
     *
     * @ApiDoc(
     *       parameters={
     *          {"name"="archive", "dataType"="integer", "required"=false, "format"="1 or 0, all entries by default", "description"="filter by archived status."},
     *          {"name"="starred", "dataType"="integer", "required"=false, "format"="1 or 0, all entries by default", "description"="filter by starred status."},
     *          {"name"="sort", "dataType"="string", "required"=false, "format"="'created' or 'updated', default 'created'", "description"="sort entries by date."},
     *          {"name"="order", "dataType"="string", "required"=false, "format"="'asc' or 'desc', default 'desc'", "description"="order of sort."},
     *          {"name"="page", "dataType"="integer", "required"=false, "format"="default '1'", "description"="what page you want."},
     *          {"name"="perPage", "dataType"="integer", "required"=false, "format"="default'30'", "description"="results per page."},
     *          {"name"="tags", "dataType"="string", "required"=false, "format"="api,rest", "description"="a list of tags url encoded. Will returns entries that matches ALL tags."},
     *       }
     * )
     *
     * @return Response
     */
    public function getEntriesAction(Request $request)
    {
        $this->validateAuthentication();

        $isArchived = (int) $request->query->get('archive');
        $isStarred = (int) $request->query->get('starred');
        $sort = $request->query->get('sort', 'created');
        $order = $request->query->get('order', 'desc');
        $page = (int) $request->query->get('page', 1);
        $perPage = (int) $request->query->get('perPage', 30);

        $pager = $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entry')
            ->findEntries($this->getUser()->getId(), (bool) $isArchived, (bool) $isStarred, $sort, $order);

        $pager->setCurrentPage($page);
        $pager->setMaxPerPage($perPage);

        $pagerfantaFactory = new PagerfantaFactory('page', 'perPage');
        $paginatedCollection = $pagerfantaFactory->createRepresentation(
            $pager,
            new Route('api_get_entries', [], UrlGeneratorInterface::ABSOLUTE_URL)
        );

        $json = $this->get('serializer')->serialize($paginatedCollection, 'json');

        return $this->renderJsonResponse($json);
    }

    /**
     * Retrieve a single entry.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     *
     * @return Response
     */
    public function getEntryAction(Entry $entry)
    {
        $this->validateAuthentication();
        $this->validateUserAccess($entry->getUser()->getId());

        $json = $this->get('serializer')->serialize($entry, 'json');

        return $this->renderJsonResponse($json);
    }

    /**
     * Create an entry.
     *
     * @ApiDoc(
     *       parameters={
     *          {"name"="url", "dataType"="string", "required"=true, "format"="http://www.test.com/article.html", "description"="Url for the entry."},
     *          {"name"="title", "dataType"="string", "required"=false, "description"="Optional, we'll get the title from the page."},
     *          {"name"="tags", "dataType"="string", "required"=false, "format"="tag1,tag2,tag3", "description"="a comma-separated list of tags."},
     *          {"name"="starred", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="entry already starred"},
     *          {"name"="archive", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="entry already archived"},
     *       }
     * )
     *
     * @return Response
     */
    public function postEntriesAction(Request $request)
    {
        $this->validateAuthentication();

        $url = $request->request->get('url');
        $isArchived = (int) $request->request->get('archive');
        $isStarred = (int) $request->request->get('starred');

        $entry = $this->get('wallabag_core.entry_repository')->findByUrlAndUserId($url, $this->getUser()->getId());

        if (false === $entry) {
            $entry = $this->get('wallabag_core.content_proxy')->updateEntry(
                new Entry($this->getUser()),
                $url
            );
        }

        $tags = $request->request->get('tags', '');
        if (!empty($tags)) {
            $this->get('wallabag_core.content_proxy')->assignTagsToEntry($entry, $tags);
        }

        if (!is_null($isStarred)) {
            $entry->setStarred((bool) $isStarred);
        }

        if (!is_null($isArchived)) {
            $entry->setArchived((bool) $isArchived);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($entry);

        $em->flush();

        $json = $this->get('serializer')->serialize($entry, 'json');

        return $this->renderJsonResponse($json);
    }

    /**
     * Change several properties of an entry.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      },
     *      parameters={
     *          {"name"="title", "dataType"="string", "required"=false},
     *          {"name"="tags", "dataType"="string", "required"=false, "format"="tag1,tag2,tag3", "description"="a comma-separated list of tags."},
     *          {"name"="archive", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="archived the entry."},
     *          {"name"="starred", "dataType"="integer", "required"=false, "format"="1 or 0", "description"="starred the entry."},
     *      }
     * )
     *
     * @return Response
     */
    public function patchEntriesAction(Entry $entry, Request $request)
    {
        $this->validateAuthentication();
        $this->validateUserAccess($entry->getUser()->getId());

        $title = $request->request->get('title');
        $isArchived = (int) $request->request->get('archive');
        $isStarred = (int) $request->request->get('starred');

        if (!is_null($title)) {
            $entry->setTitle($title);
        }

        if (!is_null($isArchived)) {
            $entry->setArchived((bool) $isArchived);
        }

        if (!is_null($isStarred)) {
            $entry->setStarred((bool) $isStarred);
        }

        $tags = $request->request->get('tags', '');
        if (!empty($tags)) {
            $this->get('wallabag_core.content_proxy')->assignTagsToEntry($entry, $tags);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $json = $this->get('serializer')->serialize($entry, 'json');

        return $this->renderJsonResponse($json);
    }

    /**
     * Delete **permanently** an entry.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     *
     * @return Response
     */
    public function deleteEntriesAction(Entry $entry)
    {
        $this->validateAuthentication();
        $this->validateUserAccess($entry->getUser()->getId());

        $em = $this->getDoctrine()->getManager();
        $em->remove($entry);
        $em->flush();

        $json = $this->get('serializer')->serialize($entry, 'json');

        return $this->renderJsonResponse($json);
    }

    /**
     * Retrieve all tags for an entry.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     *
     * @return Response
     */
    public function getEntriesTagsAction(Entry $entry)
    {
        $this->validateAuthentication();
        $this->validateUserAccess($entry->getUser()->getId());

        $json = $this->get('serializer')->serialize($entry->getTags(), 'json');

        return $this->renderJsonResponse($json);
    }

    /**
     * Add one or more tags to an entry.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      },
     *      parameters={
     *          {"name"="tags", "dataType"="string", "required"=false, "format"="tag1,tag2,tag3", "description"="a comma-separated list of tags."},
     *       }
     * )
     *
     * @return Response
     */
    public function postEntriesTagsAction(Request $request, Entry $entry)
    {
        $this->validateAuthentication();
        $this->validateUserAccess($entry->getUser()->getId());

        $tags = $request->request->get('tags', '');
        if (!empty($tags)) {
            $this->get('wallabag_core.content_proxy')->assignTagsToEntry($entry, $tags);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($entry);
        $em->flush();

        $json = $this->get('serializer')->serialize($entry, 'json');

        return $this->renderJsonResponse($json);
    }

    /**
     * Permanently remove one tag for an entry.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="tag", "dataType"="integer", "requirement"="\w+", "description"="The tag ID"},
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     *
     * @return Response
     */
    public function deleteEntriesTagsAction(Entry $entry, Tag $tag)
    {
        $this->validateAuthentication();
        $this->validateUserAccess($entry->getUser()->getId());

        $entry->removeTag($tag);
        $em = $this->getDoctrine()->getManager();
        $em->persist($entry);
        $em->flush();

        $json = $this->get('serializer')->serialize($entry, 'json');

        return $this->renderJsonResponse($json);
    }

    /**
     * Retrieve all tags.
     *
     * @ApiDoc()
     *
     * @return Response
     */
    public function getTagsAction()
    {
        $this->validateAuthentication();

        $tags = $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Tag')
            ->findAllTags($this->getUser()->getId());

        $json = $this->get('serializer')->serialize($tags, 'json');

        return $this->renderJsonResponse($json);
    }

    /**
     * Permanently remove one tag from **every** entry.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="tag", "dataType"="integer", "requirement"="\w+", "description"="The tag"}
     *      }
     * )
     *
     * @return Response
     */
    public function deleteTagAction(Tag $tag)
    {
        $this->validateAuthentication();

        $this->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entry')
            ->removeTag($this->getUser()->getId(), $tag);

        $json = $this->get('serializer')->serialize($tag, 'json');

        return $this->renderJsonResponse($json);
    }

    /**
     * Retrieve annotations for an entry.
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
        $this->validateAuthentication();

        $annotationRows = $this
                ->getDoctrine()
                ->getRepository('WallabagAnnotationBundle:Annotation')
                ->findAnnotationsByPageId($entry->getId(), $this->getUser()->getId());
        $total = count($annotationRows);
        $annotations = array('total' => $total, 'rows' => $annotationRows);

        $json = $this->get('serializer')->serialize($annotations, 'json');

        return $this->renderJsonResponse($json);
    }

    /**
     * Creates a new annotation.
     *
     * @param Entry $entry
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="ranges", "dataType"="array", "requirement"="\w+", "description"="The range array for the annotation"},
     *          {"name"="quote", "dataType"="string", "required"=false, "description"="Optional, quote for the annotation"},
     *          {"name"="text", "dataType"="string", "required"=true, "description"=""},
     *      }
     * )
     *
     * @return Response
     */
    public function postAnnotationAction(Request $request, Entry $entry)
    {
        $this->validateAuthentication();

        $data = json_decode($request->getContent(), true);

        $em = $this->getDoctrine()->getManager();

        $annotation = new Annotation($this->getUser());

        $annotation->setText($data['text']);
        if (array_key_exists('quote', $data)) {
            $annotation->setQuote($data['quote']);
        }
        if (array_key_exists('ranges', $data)) {
            $annotation->setRanges($data['ranges']);
        }

        $annotation->setEntry($entry);

        $em->persist($annotation);
        $em->flush();

        $json = $this->get('serializer')->serialize($annotation, 'json');

        return $this->renderJsonResponse($json);
    }

    /**
     * Updates an annotation.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="annotation", "dataType"="string", "requirement"="\w+", "description"="The annotation ID"}
     *      }
     * )
     *
     * @ParamConverter("annotation", class="WallabagAnnotationBundle:Annotation")
     *
     * @return Response
     */
    public function putAnnotationAction(Annotation $annotation, Request $request)
    {
        $this->validateAuthentication();

        $data = json_decode($request->getContent(), true);

        if (!is_null($data['text'])) {
            $annotation->setText($data['text']);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $json = $this->get('serializer')->serialize($annotation, 'json');

        return $this->renderJsonResponse($json);
    }

    /**
     * Removes an annotation.
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="annotation", "dataType"="string", "requirement"="\w+", "description"="The annotation ID"}
     *      }
     * )
     *
     * @ParamConverter("annotation", class="WallabagAnnotationBundle:Annotation")
     *
     * @return Response
     */
    public function deleteAnnotationAction(Annotation $annotation)
    {
        $this->validateAuthentication();

        $em = $this->getDoctrine()->getManager();
        $em->remove($annotation);
        $em->flush();

        $json = $this->get('serializer')->serialize($annotation, 'json');

        return $this->renderJsonResponse($json);
    }

    /**
     * Retrieve version number.
     *
     * @ApiDoc()
     *
     * @return Response
     */
    public function getVersionAction()
    {
        $version = $this->container->getParameter('wallabag_core.version');

        $json = $this->get('serializer')->serialize($version, 'json');

        return $this->renderJsonResponse($json);
    }

    /**
     * Validate that the first id is equal to the second one.
     * If not, throw exception. It means a user try to access information from an other user.
     *
     * @param int $requestUserId User id from the requested source
     */
    private function validateUserAccess($requestUserId)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if ($requestUserId != $user->getId()) {
            throw $this->createAccessDeniedException('Access forbidden. Entry user id: '.$requestUserId.', logged user id: '.$user->getId());
        }
    }

    /**
     * Send a JSON Response.
     * We don't use the Symfony JsonRespone, because it takes an array as parameter instead of a JSON string.
     *
     * @param string $json
     *
     * @return Response
     */
    private function renderJsonResponse($json)
    {
        return new Response($json, 200, array('application/json'));
    }
}
