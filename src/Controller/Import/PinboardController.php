<?php

namespace Wallabag\Controller\Import;

use Craue\ConfigBundle\Util\Config;
use OldSound\RabbitMqBundle\RabbitMq\Producer as RabbitMqProducer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wallabag\Controller\AbstractController;
use Wallabag\Form\Type\UploadImportType;
use Wallabag\Import\PinboardImport;
use Wallabag\Redis\Producer as RedisProducer;

class PinboardController extends AbstractController
{
    public function __construct(
        private readonly RabbitMqProducer $rabbitMqProducer,
        private readonly RedisProducer $redisProducer,
    ) {
    }

    #[Route(path: '/import/pinboard', name: 'import_pinboard', methods: ['GET', 'POST'])]
    #[IsGranted('IMPORT_ENTRIES')]
    public function indexAction(Request $request, PinboardImport $pinboard, Config $craueConfig, TranslatorInterface $translator)
    {
        $form = $this->createForm(UploadImportType::class);
        $form->handleRequest($request);

        $pinboard->setUser($this->getUser());

        if ($craueConfig->get('import_with_rabbitmq')) {
            $pinboard->setProducer($this->rabbitMqProducer);
        } elseif ($craueConfig->get('import_with_redis')) {
            $pinboard->setProducer($this->redisProducer);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            $markAsRead = $form->get('mark_as_read')->getData();
            $name = 'pinboard_' . $this->getUser()->getId() . '.json';

            if (null !== $file && \in_array($file->getClientMimeType(), $this->getParameter('wallabag.allow_mimetypes'), true) && $file->move($this->getParameter('wallabag.resource_dir'), $name)) {
                $res = $pinboard
                    ->setFilepath($this->getParameter('wallabag.resource_dir') . '/' . $name)
                    ->setMarkAsRead($markAsRead)
                    ->import();

                $message = 'flashes.import.notice.failed';

                if (true === $res) {
                    $summary = $pinboard->getSummary();
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

        return $this->render('Import/Pinboard/index.html.twig', [
            'form' => $form->createView(),
            'import' => $pinboard,
        ]);
    }
}
