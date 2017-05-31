<?php

namespace Wallabag\FederationBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wallabag\CoreBundle\Entity\Entry;

class LikedController extends Controller
{
    /**
     * @Route("/like/{entry}", name="like")
     * @param Entry $entry
     */
    public function likeAction(Entry $entry)
    {
        
    }
}
