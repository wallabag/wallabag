<?php

namespace Wallabag\ImportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wallabag\ImportBundle\Form\Type\UploadImportType;

abstract class BrowserController extends Controller
{
    /**
     * Return the service to handle the import.
     *
     * @return \Wallabag\ImportBundle\Import\ImportInterface
     */
    abstract protected function getImportService();

     /**
      * Return the template used for the form.
      *
      * @return string
      */
     abstract protected function getImportTemplate();

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
        $wallabag->setUser($this->getUser());

        if ($form->isValid()) {
            $file = $form->get('file')->getData();
            $markAsRead = $form->get('mark_as_read')->getData();
            $name = $this->getUser()->getId().'.json';

            if (null !== $file && in_array($file->getClientMimeType(), $this->getParameter('wallabag_import.allow_mimetypes')) && $file->move($this->getParameter('wallabag_import.resource_dir'), $name)) {
                $res = $wallabag
                    ->setFilepath($this->getParameter('wallabag_import.resource_dir').'/'.$name)
                    ->setMarkAsRead($markAsRead)
                    ->import();

                $message = 'flashes.import.notice.failed';

                if (true === $res) {
                    $summary = $wallabag->getSummary();
                    $message = $this->get('translator')->trans('flashes.import.notice.summary', [
                        '%imported%' => $summary['imported'],
                        '%skipped%' => $summary['skipped'],
                    ]);

                    if (0 < $summary['queued']) {
                        $message = $this->get('translator')->trans('flashes.import.notice.summary_with_queue', [
                            '%queued%' => $summary['queued'],
                        ]);
                    }

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
