<?php

namespace Wallabag\CoreBundle\Command;

use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Wallabag\UserBundle\Entity\User;

class CleanDownloadedImagesCommand extends ContainerAwareCommand
{
    /** @var SymfonyStyle */
    protected $io;

    protected $deleted = 0;

    protected function configure()
    {
        $this
            ->setName('wallabag:clean-downloaded-images')
            ->setDescription('Cleans downloaded images which are no more associated to an entry')
            ->addArgument(
                'username',
                InputArgument::OPTIONAL,
                'User to clean'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $username = $input->getArgument('username');

        if ($username) {
            try {
                $user = $this->getContainer()->get('wallabag_user.user_repository')->findOneByUserName($username);
                $this->clean($user);
            } catch (NoResultException $e) {
                $this->io->error(sprintf('User "%s" not found.', $username));

                return 1;
            }

            $this->io->success('Finished cleaning.');
        } else {
            $users = $this->getContainer()->get('wallabag_user.user_repository')->findAll();

            $this->io->text(sprintf('Cleaning through <info>%d</info> user accounts', \count($users)));

            foreach ($users as $user) {
                $this->clean($user);
            }
            $this->io->success(sprintf('Finished cleaning. %d deleted images', $this->deleted));
        }

        return 0;
    }

    private function clean(User $user)
    {
        $this->io->text(sprintf('Processing user <info>%s</info>', $user->getUsername()));

        $repo = $this->getContainer()->get('wallabag_core.entry_repository');
        $downloadImages = $this->getContainer()->get('wallabag_core.entry.download_images');
        $baseFolder = $downloadImages->getBaseFolder();

        $entries = $repo->findAllEntriesIdByUserId($user->getId());

        $deletedCount = 0;

        // first retrieve _valid_ folders from existing entries
        $hashToId = [];
        $validPaths = [];
        foreach ($entries as $entry) {
            $path = $downloadImages->getRelativePath($entry['id']);

            if (!file_exists($baseFolder . '/' . $path)) {
                continue;
            }

            // only store the hash, not the full path
            $hash = explode('/', $path)[2];
            $validPaths[] = $hash;
            $hashToId[$hash] = $entry['id'];
        }

        // then retrieve _existing_ folders in the image folder
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

        // check if existing path are valid, if not, remove all images and the folder
        foreach ($existingPaths as $existingPath) {
            if (!\in_array($existingPath, $validPaths, true)) {
                $fullPath = $baseFolder . '/' . $existingPath[0] . '/' . $existingPath[1] . '/' . $existingPath;
                $res = array_map('unlink', glob($fullPath . '/*.*'));

                rmdir($fullPath);

                $deletedCount += \count($res);

                $this->io->text(sprintf('Deleted images in <info>%s</info>: <info>%d</info>', $existingPath, \count($res)));
            }
        }

        $this->deleted += $deletedCount;

        $this->io->text(sprintf('Deleted <info>%d</info> images for user <info>%s</info>', $deletedCount, $user->getUserName()));
    }
}
