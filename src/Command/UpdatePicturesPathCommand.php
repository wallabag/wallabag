<?php

namespace Wallabag\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wallabag\Repository\EntryRepository;

class UpdatePicturesPathCommand extends Command
{
    protected static $defaultName = 'wallabag:update-pictures-path';
    protected static $defaultDescription = 'Update the path of the pictures for each entry when you changed your wallabag instance URL.';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EntryRepository $entryRepository,
        private readonly string $wallabagUrl,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument(
                'old-url',
                InputArgument::REQUIRED,
                'URL to replace'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $oldUrl = $input->getArgument('old-url');

        $query = $this->entryRepository->createQueryBuilder('e')->getQuery();
        $io->text('Retrieve existing entries');
        $i = 1;
        foreach ($query->toIterable() as $entry) {
            $content = $entry->getContent();
            if (null !== $content) {
                $entry->setContent(str_replace($oldUrl, $this->wallabagUrl, $content));
            }

            $previewPicture = $entry->getPreviewPicture();
            if (null !== $previewPicture) {
                $entry->setPreviewPicture(str_replace($oldUrl, $this->wallabagUrl, $previewPicture));
            }

            if (0 === ($i % 20)) {
                $this->entityManager->flush();
            }
            ++$i;
        }
        $this->entityManager->flush();

        $io->success('Finished updating.');

        return 0;
    }
}
