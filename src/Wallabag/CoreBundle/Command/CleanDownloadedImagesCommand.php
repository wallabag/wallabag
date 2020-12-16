<?php

namespace Wallabag\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Wallabag\CoreBundle\Helper\DownloadImages;
use Wallabag\UserBundle\Entity\User;

class CleanDownloadedImagesCommand extends ContainerAwareCommand
{
    /** @var SymfonyStyle */
    protected $io;
    protected $deleted = 0;
    /** @var DownloadImages */
    protected $downloadImages;

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
        $this->io = new SymfonyStyle($input, $output);

        $dryRun = (bool) $input->getOption('dry-run');

        $users = $this->getContainer()->get('wallabag_user.user_repository')->findAll();

        $this->io->text(sprintf('Cleaning through <info>%d</info> user accounts', \count($users)));

        if ($dryRun) {
            $this->io->text('Dry run mode <info>enabled</info> (no images will be removed)');
        }

        $this->downloadImages = $this->getContainer()->get('wallabag_core.entry.download_images');

        // retrieve _existing_ folders in the image folder
        $finder = new Finder();
        $finder
            ->directories()
            ->ignoreDotFiles(true)
            ->depth(2)
            ->in($this->downloadImages->getBaseFolder());

        $existingPaths = [];
        foreach ($finder as $file) {
            $existingPaths[] = $file->getFilename();
        }

        foreach ($users as $user) {
            $this->clean($user, $existingPaths, $dryRun);
        }

        $this->io->success(sprintf('Finished cleaning. %d deleted images', $this->deleted));

        return 0;
    }

    private function clean(User $user, array $existingPaths, bool $dryRun)
    {
        $this->io->text(sprintf('Processing user <info>%s</info>', $user->getUsername()));

        $repo = $this->getContainer()->get('wallabag_core.entry_repository');
        $baseFolder = $this->downloadImages->getBaseFolder();
        $entries = $repo->findAllEntriesIdByUserId($user->getId());

        $deletedCount = 0;

        // retrieve _valid_ folders from existing entries
        $validPaths = [];
        foreach ($entries as $entry) {
            $path = $this->downloadImages->getRelativePath($entry['id']);

            if (!file_exists($baseFolder . '/' . $path)) {
                continue;
            }

            // only store the hash, not the full path
            $validPaths[] = explode('/', $path)[2];
        }

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

                $this->io->text(sprintf('Deleted images in <info>%s</info>: <info>%d</info>', $existingPath, \count($files)));
            }
        }

        $this->deleted += $deletedCount;

        $this->io->text(sprintf('Deleted <info>%d</info> images for user <info>%s</info>', $deletedCount, $user->getUserName()));
    }
}
