<?php

namespace Wallabag\CoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class CleanDownloadedImagesCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('wallabag:clean-downloaded-images')
            ->setDescription('Cleans downloaded images which are no more associated to an entry')
            ->addOption(
               'dry-run',
               null,
               InputOption::VALUE_NONE,
               'Do not remove images, just dump counters'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $dryRun = (bool) $input->getOption('dry-run');

        if ($dryRun) {
            $io->text('Dry run mode <info>enabled</info> (no images will be removed)');
        }

        $downloadImages = $this->getContainer()->get('wallabag_core.entry.download_images');
        $baseFolder = $downloadImages->getBaseFolder();

        $io->text('Retrieve existing images');

        // retrieve _existing_ folders in the image folder
        $finder = new Finder();
        $finder
            ->directories()
            ->ignoreDotFiles(true)
            ->depth(2)
            ->in($baseFolder);

        $existingPaths = [];
        foreach ($finder as $file) {
            $existingPaths[] = $file->getFilename();
        }

        $io->text(sprintf('  -> <info>%d</info> images found', \count($existingPaths)));

        $io->text('Retrieve valid folders attached to a user');

        $entries = $this->getContainer()->get('wallabag_core.entry_repository')->findAllEntriesIdByUserId();

        // retrieve _valid_ folders from existing entries
        $validPaths = [];
        foreach ($entries as $entry) {
            $path = $downloadImages->getRelativePath($entry['id']);

            if (!file_exists($baseFolder . '/' . $path)) {
                continue;
            }

            // only store the hash, not the full path
            $validPaths[] = explode('/', $path)[2];
        }

        $io->text(sprintf('  -> <info>%d</info> folders found', \count($validPaths)));

        $deletedCount = 0;

        $io->text('Remove images');

        // check if existing path are valid, if not, remove all images and the folder
        foreach ($existingPaths as $existingPath) {
            if (!\in_array($existingPath, $validPaths, true)) {
                $fullPath = $baseFolder . '/' . $existingPath[0] . '/' . $existingPath[1] . '/' . $existingPath;
                $files = glob($fullPath . '/*.*');

                if (!$dryRun) {
                    array_map('unlink', $files);
                    rmdir($fullPath);
                }

                $deletedCount += \count($files);

                $io->text(sprintf('Deleted images in <info>%s</info>: <info>%d</info>', $existingPath, \count($files)));
            }
        }

        $io->success(sprintf('Finished cleaning. %d deleted images', $deletedCount));

        return 0;
    }
}
