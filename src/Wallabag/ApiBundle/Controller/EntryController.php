<?php

namespace Wallabag\ApiBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wallabag\CoreBundle\Entity\Entries;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Patch;
use Wallabag\CoreBundle\Entity\Users;

class EntryController extends Controller
{
    /**
     * Fetches an entry for a given user
     *
     * @Get("/u/{user}/entry/{entry}")
     * @ApiDoc(
     *      requirements={
     *          {"name"="user", "dataType"="string", "requirement"="\w+", "description"="The user ID"},
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     * @return Entries
     */
    public function getAction(Users $user, Entries $entry)
    {
        return $entry;
    }

    /**
     * Deletes an entry for a given user
     *
     * @Delete("/u/{user}/entry/{entry}")
     * @ApiDoc(
     *      requirements={
     *          {"name"="user", "dataType"="string", "requirement"="\w+", "description"="The user ID"},
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     */
    public function deleteAction(Users $user, Entries $entry)
    {

    }

    /**
     * Changes several properties of an entry. I.E tags, archived, starred and deleted status
     *
     * @Patch("/u/{user}/entry/{entry}")
     * @ApiDoc(
     *      requirements={
     *          {"name"="user", "dataType"="string", "requirement"="\w+", "description"="The user ID"},
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     */
    public function patchAction(Users $user, Entries $entry)
    {

    }
}
