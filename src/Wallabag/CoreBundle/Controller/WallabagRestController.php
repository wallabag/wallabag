<?php

namespace Wallabag\CoreBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Wallabag\CoreBundle\Entity\Entries;
use Wallabag\CoreBundle\Entity\Tags;
use Wallabag\CoreBundle\Entity\Users;

class WallabagRestController
{

    /**
     * Fetches all entries
     *
     * @ApiDoc(
     * )
     * @return Entries
     */
    public function getEntriesAction()
    {

    }

    /**
     * Fetches an entry
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
     * Deletes an entry
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
     * Changes several properties of an entry. I.E tags, archived, starred and deleted status
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     */
    public function patchEntriesAction(Entries $entry)
    {

    }

    /**
     * Saves a new entry
     *
     * @ApiDoc(
     * )
     */
    public function postEntriesAction()
    {

    }

    /**
     * Gets tags for an entry
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     */
    public function getEntriesTagsAction(Entries $entry) {

    }

    /**
     * Saves new tag for an entry
     *
     * @ApiDoc(
     *      requirements={
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     */
    public function postEntriesTagsAction(Entries $entry) {

    }

    /**
     * Remove tag for an entry
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
     * Gets tags for a user
     *
     * @ApiDoc(
     * )
     */
    public function getTagsAction() {

    }

    /**
     * Gets one tag
     *
     * @ApiDoc(
     *          {"name"="tag", "dataType"="string", "requirement"="\w+", "description"="The tag"}
     * )
     */
    public function getTagAction(Tags $tag) {

    }

    /**
     * Delete tag
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