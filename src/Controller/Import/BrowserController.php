<?php

namespace App\Controller\Import;

use App\Form\Type\UploadImportType;
use App\Import\ImportInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class BrowserController extends AbstractController
{
    /**
     * @Route("/import/browser", name="import_browser")
     *
     * @return Response
     */
    public function indexAction(Request $request, TranslatorInterface $translator)
    {
        $form = $this->createForm(UploadImportType::class);
        $form->handleRequest($request);

        $wallabag = $this->getImportService();
        $wallabag->setUser($this->getUser());

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            $markAsRead = $form->get('mark_as_read')->getData();
            $name = $this->getUser()->getId() . '.json';

            if (null !== $file && \in_array($file->getClientMimeType(), $this->getParameter('wallabag.import.allow_mimetypes'), true) && $file->move($this->getParameter('wallabag.import.resource_dir'), $name)) {
                $res = $wallabag
                    ->setFilepath($this->getParameter('wallabag.import.resource_dir') . '/' . $name)
                    ->setMarkAsRead($markAsRead)
                    ->import();

                $message = 'flashes.import.notice.failed';

                if (true === $res) {
                    $summary = $wallabag->getSummary();
                    $message = $translator->trans('flashes.import.notice.summary', [
                        '%imported%' => $summary['imported'],
                        '%skipped%' => $summary['skipped'],
                    ]);

                    if (0 < $summary['queued']) {
                        $message = $translator->trans('flashes.import.notice.summary_with_queue', [
                            '%queued%' => $summary['queued'],
                        ]);
                    }

                    unlink($this->getParameter('wallabag.import.resource_dir') . '/' . $name);
                }

                $this->addFlash('notice', $message);

                return $this->redirect($this->generateUrl('homepage'));
            }
            $this->addFlash('notice', 'flashes.import.notice.failed_on_file');
        }

        return $this->render($this->getImportTemplate(), [
            'form' => $form->createView(),
            'import' => $wallabag,
        ]);
    }

    /**
     * Return the service to handle the import.
     *
     * @return ImportInterface
     */
    abstract protected function getImportService();

    /**
     * Return the template used for the form.
     *
     * @return string
     */
    abstract protected function getImportTemplate();
}
