<?php

namespace Wallabag\ApiBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wallabag\CoreBundle\Entity\Entries;
use FOS\RestBundle\Controller\Annotations\Get;
use Wallabag\CoreBundle\Entity\Users;

class EntryController extends Controller
{
    /**
     * Fetch an entry for a given user
     *
     * @Get("/u/{user}/entry/{entry}")
     * @ApiDoc(
     *      requirements={
     *          {"name"="user", "dataType"="string", "requirement"="\w+", "description"="The username"},
     *          {"name"="entry", "dataType"="integer", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     * @return Entries
     */
    public function getAction(Users $user, Entries $entry)
    {
        return $entry;
    }
}
