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
use Wallabag\Import\OmnivoreImport;
use Wallabag\Redis\Producer as RedisProducer;

class OmnivoreController extends AbstractController
{
    public function __construct(
        private readonly RabbitMqProducer $rabbitMqProducer,
        private readonly RedisProducer $redisProducer,
        private readonly array $allowMimetypes,
        private readonly string $resourceDir,
    ) {
    }

    #[Route(path: '/import/omnivore', name: 'import_omnivore', methods: ['GET', 'POST'])]
    #[IsGranted('IMPORT_ENTRIES')]
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

            if (null !== $file && \in_array($file->getClientMimeType(), $this->allowMimetypes, true) && $file->move($this->resourceDir, $name)) {
                try {
                    $res = $omnivore
                        ->setFilepath($this->resourceDir . '/' . $name)
                        ->setMarkAsRead($markAsRead)
                        ->import();
                } finally {
                    // never leave the uploaded file on disk, whatever the import result is
                    @unlink($this->resourceDir . '/' . $name);
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

                return $this->redirectToDefaultHomepage();
            }

            $this->addFlash('notice', 'flashes.import.notice.failed_on_file');
        }

        return $this->render('Import/Omnivore/index.html.twig', [
            'form' => $form->createView(),
            'import' => $omnivore,
        ]);
    }
}
