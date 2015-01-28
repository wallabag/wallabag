<?php

namespace Wallabag\CoreBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wallabag\CoreBundle\Entity\Entries;

class ApiController extends Controller
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="This is a demo method. Just remove it",
     * )
     */
    public function getEntryAction()
    {
        return new Entries('Blobby');
    }
}
