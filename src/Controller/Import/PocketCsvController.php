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
use Wallabag\Import\PocketCsvImport;
use Wallabag\Redis\Producer as RedisProducer;

class PocketCsvController extends AbstractController
{
    public function __construct(
        private readonly PocketCsvImport $pocketCsvImport,
        private readonly Config $craueConfig,
        private readonly RabbitMqProducer $rabbitMqProducer,
        private readonly RedisProducer $redisProducer,
        private readonly array $allowMimetypes,
        private readonly string $resourceDir,
    ) {
    }

    #[Route(path: '/import/pocket_csv', name: 'import_pocket_csv', methods: ['GET', 'POST'])]
    #[IsGranted('IMPORT_ENTRIES')]
    public function indexAction(Request $request, TranslatorInterface $translator)
    {
        $form = $this->createForm(UploadImportType::class);
        $form->handleRequest($request);

        $this->pocketCsvImport->setUser($this->getUser());

        if ($this->craueConfig->get('import_with_rabbitmq')) {
            $this->pocketCsvImport->setProducer($this->rabbitMqProducer);
        } elseif ($this->craueConfig->get('import_with_redis')) {
            $this->pocketCsvImport->setProducer($this->redisProducer);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            $markAsRead = $form->get('mark_as_read')->getData();
            $name = 'pocket_' . $this->getUser()->getId() . '.csv';

            if (null !== $file && \in_array($file->getClientMimeType(), $this->allowMimetypes, true) && $file->move($this->resourceDir, $name)) {
                $res = $this->pocketCsvImport
                    ->setFilepath($this->resourceDir . '/' . $name)
                    ->setMarkAsRead($markAsRead)
                    ->import();

                $message = 'flashes.import.notice.failed';

                if (true === $res) {
                    $summary = $this->pocketCsvImport->getSummary();
                    $message = $translator->trans('flashes.import.notice.summary', [
                        '%imported%' => $summary['imported'],
                        '%skipped%' => $summary['skipped'],
                    ]);

                    if (0 < $summary['queued']) {
                        $message = $translator->trans('flashes.import.notice.summary_with_queue', [
                            '%queued%' => $summary['queued'],
                        ]);
                    }

                    unlink($this->resourceDir . '/' . $name);
                }

                $this->addFlash('notice', $message);

                return $this->redirect($this->generateUrl('homepage'));
            }

            $this->addFlash('notice', 'flashes.import.notice.failed_on_file');
        }

        return $this->render('Import/PocketCsv/index.html.twig', [
            'form' => $form->createView(),
            'import' => $this->pocketCsvImport,
        ]);
    }

    protected function getImportService()
    {
        if ($this->craueConfig->get('import_with_rabbitmq')) {
            $this->pocketCsvImport->setProducer($this->rabbitMqProducer);
        } elseif ($this->craueConfig->get('import_with_redis')) {
            $this->pocketCsvImport->setProducer($this->redisProducer);
        }

        return $this->pocketCsvImport;
    }

    protected function getImportTemplate()
    {
        return 'Import/PocketCsv/index.html.twig';
    }
}
