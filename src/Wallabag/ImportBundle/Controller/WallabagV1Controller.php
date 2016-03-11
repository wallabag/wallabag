<?php

namespace Wallabag\ImportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\ImportBundle\Form\Type\UploadImportType;

class WallabagV1Controller extends Controller
{
    /**
     * @Route("/wallabag-v1", name="import_wallabag_v1")
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(UploadImportType::class);
        $form->handleRequest($request);

        $wallabag = $this->get('wallabag_import.wallabag_v1.import');

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

                $message = 'flashes.import.notice.failed';

                if (true === $res) {
                    $summary = $wallabag->getSummary();
                    $message = $this->get('translator')->trans('flashes.import.notice.summary', array(
                        '%imported%' => $summary['imported'],
                        '%skipped%' => $summary['skipped'],
                    ));

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
                    'flashes.import.notice.failed_on_file'
                );
            }
        }

        return $this->render('WallabagImportBundle:WallabagV1:index.html.twig', [
            'form' => $form->createView(),
            'import' => $wallabag,
        ]);
    }
}
