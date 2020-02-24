<?php

namespace Wallabag\ImportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\ImportBundle\Form\Type\UploadImportType;

class ReadabilityController extends Controller
{
    /**
     * @Route("/readability", name="import_readability")
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(UploadImportType::class);
        $form->handleRequest($request);

        $readability = $this->get('wallabag_import.readability.import');
        $readability->setUser($this->getUser());

        if ($this->get('craue_config')->get('import_with_rabbitmq')) {
            $readability->setProducer($this->get('old_sound_rabbit_mq.import_readability_producer'));
        } elseif ($this->get('craue_config')->get('import_with_redis')) {
            $readability->setProducer($this->get('wallabag_import.producer.redis.readability'));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            $markAsRead = $form->get('mark_as_read')->getData();
            $name = 'readability_' . $this->getUser()->getId() . '.json';

            if (null !== $file && \in_array($file->getClientMimeType(), $this->getParameter('wallabag_import.allow_mimetypes'), true) && $file->move($this->getParameter('wallabag_import.resource_dir'), $name)) {
                $res = $readability
                    ->setFilepath($this->getParameter('wallabag_import.resource_dir') . '/' . $name)
                    ->setMarkAsRead($markAsRead)
                    ->import();

                $message = 'flashes.import.notice.failed';

                if (true === $res) {
                    $summary = $readability->getSummary();
                    $message = $this->get('translator')->trans('flashes.import.notice.summary', [
                        '%imported%' => $summary['imported'],
                        '%skipped%' => $summary['skipped'],
                    ]);

                    if (0 < $summary['queued']) {
                        $message = $this->get('translator')->trans('flashes.import.notice.summary_with_queue', [
                            '%queued%' => $summary['queued'],
                        ]);
                    }

                    unlink($this->getParameter('wallabag_import.resource_dir') . '/' . $name);
                }

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $message
                );

                return $this->redirect($this->generateUrl('homepage'));
            }

            $this->get('session')->getFlashBag()->add(
                'notice',
                'flashes.import.notice.failed_on_file'
            );
        }

        return $this->render('WallabagImportBundle:Readability:index.html.twig', [
            'form' => $form->createView(),
            'import' => $readability,
        ]);
    }
}
