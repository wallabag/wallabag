<?php

namespace Wallabag\ImportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wallabag\ImportBundle\Form\Type\UploadImportType;

class BrowserController extends Controller
{
    /**
     * Return the service to handle the import.
     *
     * @return \Wallabag\ImportBundle\Import\ImportInterface
     */
    protected function getImportService()
    {
        return $this->get('wallabag_import.browser.import');
    }

     /**
      * Return the template used for the form.
      *
      * @return string
      */
     protected function getImportTemplate()
     {
         return 'WallabagImportBundle:Browser:index.html.twig';
     }

    /**
     * @Route("/browser", name="import_browser")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(UploadImportType::class);
        $form->handleRequest($request);

        $wallabag = $this->getImportService();

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
                    // TODO : Pluralize these messages
                    $message = $this->get('translator')->trans('flashes.import.notice.summary', [
                        '%imported%' => $summary['imported'],
                        '%skipped%' => $summary['skipped'],
                    ]);

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

        return $this->render($this->getImportTemplate(), [
            'form' => $form->createView(),
            'import' => $wallabag,
        ]);
    }
}
