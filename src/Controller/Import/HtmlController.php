<?php

namespace Wallabag\Controller\Import;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wallabag\Controller\AbstractController;
use Wallabag\Form\Type\UploadImportType;
use Wallabag\Import\ImportInterface;

abstract class HtmlController extends AbstractController
{
    /**
     * @return Response
     */
    #[Route(path: '/import/html', name: 'import_html', methods: ['GET', 'POST'])]
    #[IsGranted('IMPORT_ENTRIES')]
    public function indexAction(Request $request, TranslatorInterface $translator)
    {
        $form = $this->createForm(UploadImportType::class);
        $form->handleRequest($request);

        $wallabag = $this->getImportService();
        $wallabag->setUser($this->getUser());

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            $markAsRead = $form->get('mark_as_read')->getData();
            $name = $this->getUser()->getId() . '.html';

            if (null !== $file && \in_array($file->getClientMimeType(), $this->getParameter('wallabag.allow_mimetypes'), true) && $file->move($this->getParameter('wallabag.resource_dir'), $name)) {
                $res = $wallabag
                    ->setFilepath($this->getParameter('wallabag.resource_dir') . '/' . $name)
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

                    unlink($this->getParameter('wallabag.resource_dir') . '/' . $name);
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
