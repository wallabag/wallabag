<?php

namespace Wallabag\CoreBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Service\Extractor;

class WallabagRestController extends Controller
{
    /**
     * Retrieve salt for a giver user.
     *
     * @ApiDoc(
     *       parameters={
     *          {"name"="username", "dataType"="string", "required"=true, "description"="username"}
     *       }
     * )
     * @return array
     */
    public function getSaltAction($username)
    {
        $user = $this
            ->getDoctrine()
            ->getRepository('WallabagCoreBundle:User')
            ->findOneByUsername($username);

        if (is_null($user)) {
            throw $this->createNotFoundException();
        }

        return array($user->getSalt() ?: null);
    }
    /**
     * Retrieve all entries. It could be filtered by many options.
     *
     * @ApiDoc(
     *       parameters={
     *          {"name"="archive", "dataType"="boolean", "required"=false, "format"="true or false, all entries by default", "description"="filter by archived status."},
     *          {"name"="star", "dataType"="boolean", "required"=false, "format"="true or false, all entries by default", "description"="filter by starred status."},
     *          {"name"="sort", "dataType"="string", "required"=false, "format"="'created' or 'updated', default 'created'", "description"="sort entries by date."},
     *          {"name"="order", "dataType"="string", "required"=false, "format"="'asc' or 'desc', default 'desc'", "description"="order of sort."},
     *          {"name"="page", "dataType"="integer", "required"=false, "format"="default '1'", "description"="what page you want."},
     *          {"name"="perPage", "dataType"="integer", "required"=false, "format"="default'30'", "description"="results per page."},
     *          {"name"="tags", "dataType"="string", "required"=false, "format"="api%2Crest", "description"="a list of tags url encoded. Will returns entries that matches ALL tags."},
     *       }
     * )
     * @return Entry
     */
    public function getEntriesAction(Request $request)
    {
        $isArchived = $request->query->get('archive');
        $isStarred  = $request->query->get('star');
        $sort       = $request->query->get('sort', 'created');
        $order      = $request->query->get('order', 'desc');
        $page       = $request->query->get('page', 1);
        $perPage    = $request->query->get('perPage', 30);
        $tags       = $request->query->get('tags', array());

        $entries = $this
            ->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entry')
            ->findEntries($this->getUser()->getId(), $isArchived, $isStarred, $sort, $order);

        if (!($entries)) {
            throw $this->createNotFoundException();
        }

        $json = $this->get('serializer')->serialize($entries, 'json');

        return new Response($json, 200, array('application/json'));
    }

    /**
     * Retrieve a single entry
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     * @return Entry
     */
    public function getEntryAction(Entry $entry)
    {
        $json = $this->get('serializer')->serialize($entry, 'json');

        return new Response($json, 200, array('application/json'));
    }

    /**
     * Create an entry
     *
     * @ApiDoc(
     *       parameters={
     *          {"name"="url", "dataType"="string", "required"=true, "format"="http://www.test.com/article.html", "description"="Url for the entry."},
     *          {"name"="title", "dataType"="string", "required"=false, "description"="Optional, we'll get the title from the page."},
     *          {"name"="tags", "dataType"="string", "required"=false, "format"="tag1,tag2,tag3", "description"="a comma-separated list of tags."},
     *       }
     * )
     * @return Entry
     */
    public function postEntriesAction(Request $request)
    {
        //TODO gérer si on passe les tags
        $url = $request->request->get('url');

        $content = Extractor::extract($url);
        $entry = new Entry($this->getUser());
        $entry->setUrl($url);
        $entry->setTitle($request->request->get('title') ?: $content->getTitle());
        $entry->setContent($content->getBody());
        $em = $this->getDoctrine()->getManager();
        $em->persist($entry);
        $em->flush();

        $json = $this->get('serializer')->serialize($entry, 'json');

        return new Response($json, 200, array('application/json'));
    }

    /**
     * Change several properties of an entry
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      },
     *      parameters={
     *          {"name"="title", "dataType"="string", "required"=false},
     *          {"name"="tags", "dataType"="string", "required"=false, "format"="tag1,tag2,tag3", "description"="a comma-separated list of tags."},
     *          {"name"="archive", "dataType"="boolean", "required"=false, "format"="true or false", "description"="archived the entry."},
     *          {"name"="star", "dataType"="boolean", "required"=false, "format"="true or false", "description"="starred the entry."},
     *      }
     * )
     * @return Entry
     */
    public function patchEntriesAction(Entry $entry, Request $request)
    {
        $title      = $request->request->get("title");
        $tags       = $request->request->get("tags", array());
        $isArchived = $request->request->get("archive");
        $isStarred  = $request->request->get("star");

        if (!is_null($title)) {
            $entry->setTitle($title);
        }

        if (!is_null($isArchived)) {
            $entry->setArchived($isArchived);
        }

        if (!is_null($isStarred)) {
            $entry->setStarred($isStarred);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $entry;
    }

    /**
     * Delete **permanently** an entry
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     * @return Entry
     */
    public function deleteEntriesAction(Entry $entry)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($entry);
        $em->flush();

        $json = $this->get('serializer')->serialize($entry, 'json');

        return new Response($json, 200, array('application/json'));
    }

    /**
     * Retrieve all tags for an entry
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     */
    public function getEntriesTagsAction(Entry $entry)
    {
        $json = $this->get('serializer')->serialize($entry->getTags(), 'json');

        return new Response($json, 200, array('application/json'));
    }

    /**
     * Add one or more tags to an entry
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      },
     *      parameters={
     *          {"name"="tags", "dataType"="string", "required"=false, "format"="tag1,tag2,tag3", "description"="a comma-separated list of tags."},
     *       }
     * )
     */
    public function postEntriesTagsAction(Entry $entry)
    {
    }

    /**
     * Permanently remove one tag for an entry
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="tag", "dataType"="string", "requirement"="\w+", "description"="The tag"},
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     */
    public function deleteEntriesTagsAction(Entry $entry, Tag $tag)
    {
    }

    /**
     * Retrieve all tags
     *
     * @ApiDoc(
     *          {"name"="user", "dataType"="integer", "requirement"="\w+", "description"="The user ID"}
     * )
     */
    public function getTagsUserAction()
    {
    }

    /**
     * Permanently remove one tag from **every** entry
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="tag", "dataType"="string", "requirement"="\w+", "description"="The tag"}
     *      }
     * )
     */
    public function deleteTagAction(Tag $tag)
    {
    }
}
