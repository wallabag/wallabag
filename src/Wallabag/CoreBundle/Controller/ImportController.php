<?php

namespace Wallabag\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\CoreBundle\Command\ImportCommand;

class ImportController extends Controller
{
    /**
     * @param Request $request
     *
     * @Route("/import", name="import")
     */
    public function importAction(Request $request)
    {
        $command = new ImportCommand();
        $command->setContainer($this->container);
        $input = new ArrayInput(array('userId' => $this->getUser()->getId()));
        $return = $command->run($input, new NullOutput());

        if ($return == 0) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Import successful'
            );
        } else {
            $this->get('session')->getFlashBag()->add(
                'warning',
                'Import failed'
            );
        }

        return $this->redirect('/');
    }
}
