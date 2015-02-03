<?php

namespace Wallabag\CoreBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\CoreBundle\Entity\Entries;
use Wallabag\CoreBundle\Entity\Tags;
use Wallabag\CoreBundle\Service\Extractor;

class WallabagRestController extends Controller
{
    /**
     * Retrieve all entries. It could be filtered by many options.
     *
     * @ApiDoc(
     *       parameters={
     *          {"name"="archive", "dataType"="integer", "required"=false, "format"="'0' or '1', default: not filtered", "description"="filter by archived status."},
     *          {"name"="star", "dataType"="integer", "required"=false, "format"="'0' or '1', default: not filtered", "description"="filter by starred status."},
     *          {"name"="delete", "dataType"="integer", "required"=false, "format"="'0' or '1', default: not filtered", "description"="filter by deleted status."},
     *          {"name"="sort", "dataType"="string", "required"=false, "format"="'created' or 'updated', default 'created'", "description"="sort entries by date."},
     *          {"name"="order", "dataType"="string", "required"=false, "format"="'asc' or 'desc', default 'desc'", "description"="order of sort."},
     *          {"name"="page", "dataType"="integer", "required"=false, "format"="default '1'", "description"="what page you want."},
     *          {"name"="perPage", "dataType"="integer", "required"=false, "format"="default'30'", "description"="results per page."},
     *          {"name"="tags", "dataType"="string", "required"=false, "format"="api%2Crest", "description"="a list of tags url encoded. Will returns entries that matches ALL tags."},
     *       }
     * )
     * @return Entries
     */
    public function getEntriesAction(Request $request)
    {
        $isArchived = $request->query->get('archive', -1);
        $isStarred  = $request->query->get('star', -1);
        $isDeleted  = $request->query->get('delete', -1);
        $sort       = $request->query->get('sort', 'created');
        $order      = $request->query->get('order', 'desc');
        $page       = $request->query->get('page', 1);
        $perPage    = $request->query->get('perPage', 30);
        $tags       = $request->query->get('tags', array());

        $entries = $this
            ->getDoctrine()
            ->getRepository('WallabagCoreBundle:Entries')
            ->findEntries(1, (int) $isArchived, (int) $isStarred, (int) $isDeleted, $sort, $order);

        if (!is_array($entries)) {
            throw $this->createNotFoundException();
        }

        return $entries;
    }

    /**
     * Retrieve a single entry
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     * @return Entries
     */
    public function getEntryAction(Entries $entry)
    {
        return $entry;
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
     */
    public function postEntriesAction(Request $request)
    {
        //TODO la récup ne marche pas
        //TODO gérer si on passe le titre
        //TODO gérer si on passe les tags
        //TODO ne pas avoir du code comme ça qui doit se trouver dans le Repository
        $url = $request->request->get('url');

        $content = Extractor::extract($url);
        $entry = new Entries();
        $entry->setUserId(1);
        $entry->setUrl($url);
        $entry->setTitle($content->getTitle());
        $entry->setContent($content->getBody());
        $em = $this->getDoctrine()->getManager();
        $em->persist($entry);
        $em->flush();

        return $entry;
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
     *          {"name"="archive", "dataType"="integer", "required"=false, "format"="'0' or '1', default '0'", "description"="archived the entry."},
     *          {"name"="star", "dataType"="integer", "required"=false, "format"="'0' or '1', default '0'", "description"="starred the entry."},
     *          {"name"="delete", "dataType"="integer", "required"=false, "format"="'0' or '1', default '0'", "description"="flag as deleted. Default false. In case that you don't want to *really* remove it.."},
     *       }
     * )
     */
    public function patchEntriesAction(Entries $entry, Request $request)
    {
        $isStarred = $request->get('star', -1);
        $isArchived = $request->get('archive', -1);
        $title = $request->get('title', -1);

        if ($isStarred == '0' || $isStarred == '1') {
            $entry->setIsFav($isStarred);
        }
        if ($isArchived == '0' || $isArchived == '1') {
            $entry->setIsRead($isArchived);
        }
        if (is_string($title)) {
            $entry->setTitle($title);
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
     */
    public function deleteEntriesAction(Entries $entry)
    {
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
    public function getEntriesTagsAction(Entries $entry)
    {
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
    public function postEntriesTagsAction(Entries $entry)
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
    public function deleteEntriesTagsAction(Entries $entry, Tags $tag)
    {
    }

    /**
     * Retrieve all tags
     *
     * @ApiDoc(
     * )
     */
    public function getTagsAction()
    {
    }

    /**
     * Retrieve a single tag
     *
     * @ApiDoc(
     *       requirements={
     *          {"name"="tag", "dataType"="string", "requirement"="\w+", "description"="The tag"}
     *       }
     * )
     */
    public function getTagAction(Tags $tag)
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
    public function deleteTagAction(Tags $tag)
    {
    }
}
