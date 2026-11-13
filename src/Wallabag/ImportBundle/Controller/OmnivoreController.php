<?php

namespace Wallabag\ImportBundle\Controller;

use Craue\ConfigBundle\Util\Config;
use OldSound\RabbitMqBundle\RabbitMq\Producer as RabbitMqProducer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wallabag\ImportBundle\Form\Type\UploadImportType;
use Wallabag\ImportBundle\Import\OmnivoreImport;
use Wallabag\ImportBundle\Redis\Producer as RedisProducer;

class OmnivoreController extends AbstractController
{
    private RabbitMqProducer $rabbitMqProducer;
    private RedisProducer $redisProducer;

    public function __construct(RabbitMqProducer $rabbitMqProducer, RedisProducer $redisProducer)
    {
        $this->rabbitMqProducer = $rabbitMqProducer;
        $this->redisProducer = $redisProducer;
    }

    /**
     * @Route("/omnivore", name="import_omnivore")
     */
    public function indexAction(Request $request, OmnivoreImport $omnivore, Config $craueConfig, TranslatorInterface $translator)
    {
        $form = $this->createForm(UploadImportType::class);
        $form->handleRequest($request);

        $omnivore->setUser($this->getUser());

        if ($craueConfig->get('import_with_rabbitmq')) {
            $omnivore->setProducer($this->rabbitMqProducer);
        } elseif ($craueConfig->get('import_with_redis')) {
            $omnivore->setProducer($this->redisProducer);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            $markAsRead = $form->get('mark_as_read')->getData();
            $name = bin2hex(random_bytes(16)) . '.json';

            if (null !== $file && \in_array($file->getClientMimeType(), $this->getParameter('wallabag_import.allow_mimetypes'), true) && $file->move($this->getParameter('wallabag_import.resource_dir'), $name)) {
                try {
                    $res = $omnivore
                        ->setFilepath($this->getParameter('wallabag_import.resource_dir') . '/' . $name)
                        ->setMarkAsRead($markAsRead)
                        ->import();
                } finally {
                    // never leave the uploaded file on disk, whatever the import result is
                    @unlink($this->getParameter('wallabag_import.resource_dir') . '/' . $name);
                }

                $message = 'flashes.import.notice.failed';

                if (true === $res) {
                    $summary = $omnivore->getSummary();
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

        return $this->render('@WallabagImport/Omnivore/index.html.twig', [
            'form' => $form->createView(),
            'import' => $omnivore,
        ]);
    }
}
