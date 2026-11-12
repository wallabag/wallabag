<?php

namespace Wallabag\ImportBundle\Controller;

use Craue\ConfigBundle\Util\Config;
use OldSound\RabbitMqBundle\RabbitMq\Producer as RabbitMqProducer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wallabag\ImportBundle\Form\Type\UploadImportType;
use Wallabag\ImportBundle\Import\PinboardImport;
use Wallabag\ImportBundle\Redis\Producer as RedisProducer;

class PinboardController extends AbstractController
{
    private RabbitMqProducer $rabbitMqProducer;
    private RedisProducer $redisProducer;

    public function __construct(RabbitMqProducer $rabbitMqProducer, RedisProducer $redisProducer)
    {
        $this->rabbitMqProducer = $rabbitMqProducer;
        $this->redisProducer = $redisProducer;
    }

    /**
     * @Route("/pinboard", name="import_pinboard")
     */
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
            $name = bin2hex(random_bytes(16)) . '.json';

            if (null !== $file && \in_array($file->getClientMimeType(), $this->getParameter('wallabag_import.allow_mimetypes'), true) && $file->move($this->getParameter('wallabag_import.resource_dir'), $name)) {
                try {
                    $res = $pinboard
                        ->setFilepath($this->getParameter('wallabag_import.resource_dir') . '/' . $name)
                        ->setMarkAsRead($markAsRead)
                        ->import();
                } finally {
                    // never leave the uploaded file on disk, whatever the import result is
                    @unlink($this->getParameter('wallabag_import.resource_dir') . '/' . $name);
                }

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
                }

                $this->addFlash('notice', $message);

                return $this->redirect($this->generateUrl('homepage'));
            }

            $this->addFlash('notice', 'flashes.import.notice.failed_on_file');
        }

        return $this->render('@WallabagImport/Pinboard/index.html.twig', [
            'form' => $form->createView(),
            'import' => $pinboard,
        ]);
    }
}
