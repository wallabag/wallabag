<?php

namespace Wallabag\ImportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\ImportBundle\Command\ImportCommand;
use Wallabag\ImportBundle\Form\Type\UploadImportType;

class ImportController extends Controller
{
    /**
     * @Route("/import", name="import")
     */
    public function importAction(Request $request)
    {
        $importForm = $this->createForm(new UploadImportType());
        $importForm->handleRequest($request);
        $user = $this->getUser();

        if ($importForm->isValid()) {
            $file = $importForm->get('file')->getData();
            $name = $user->getId().'.json';
            $dir = __DIR__.'/../../../../web/uploads/import';

            if (in_array($file->getMimeType(), $this->getParameter('wallabag_import.allow_mimetypes')) && $file->move($dir, $name)) {
                $command = new ImportCommand();
                $command->setContainer($this->container);
                $input = new ArrayInput(array('userId' => $user->getId()));
                $return = $command->run($input, new NullOutput());

                if ($return == 0) {
                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        'Import successful'
                    );
                } else {
                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        'Import failed'
                    );
                }

                return $this->redirect('/');
            } else {
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Error while processing import. Please verify your import file.'
                );
            }
        }

        return $this->render('WallabagImportBundle:Import:index.html.twig', array(
            'form' => array(
                'import' => $importForm->createView(), ),
        ));
    }
}
