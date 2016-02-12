<?php

namespace Wallabag\ImportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\ImportBundle\Form\Type\UploadImportType;

class WallabagV2Controller extends Controller
{
    /**
     * @Route("/wallabag-v2", name="import_wallabag_v2")
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(UploadImportType::class);
        $form->handleRequest($request);

        $wallabag = $this->get('wallabag_import.wallabag_v2.import');

        if ($form->isValid()) {
            $file = $form->get('file')->getData();
            $markAsRead = $form->get('mark_as_read')->getData();
            $name = $this->getUser()->getId().'.json';

            if (in_array($file->getClientMimeType(), $this->getParameter('wallabag_import.allow_mimetypes')) && $file->move($this->getParameter('wallabag_import.resource_dir'), $name)) {
                $res = $wallabag
                    ->setUser($this->getUser())
                    ->setFilepath($this->getParameter('wallabag_import.resource_dir').'/'.$name)
                    ->setMarkAsRead($markAsRead)
                    ->import();

                $message = 'Import failed, please try again.';

                if (true === $res) {
                    $summary = $wallabag->getSummary();
                    $message = 'Import summary: '.$summary['imported'].' imported, '.$summary['skipped'].' already saved.';

                    unlink($this->getParameter('wallabag_import.resource_dir').'/'.$name);
                }

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $message
                );

                return $this->redirect($this->generateUrl('homepage'));
            } else {
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Error while processing import. Please verify your import file.'
                );
            }
        }

        return $this->render('WallabagImportBundle:WallabagV2:index.html.twig', [
            'form' => $form->createView(),
            'import' => $wallabag,
        ]);
    }
}
