<?php

namespace Wallabag\ImportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Tools\Utils;

class ImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('wallabag:import')
            ->setDescription('Import entries from JSON file')
            ->addArgument(
                'userId',
                InputArgument::REQUIRED,
                'user ID to populate'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $now = new \DateTime();
        $output->writeln('<comment>Start : '.$now->format('d-m-Y G:i:s').' ---</comment>');

        // Importing CSV on DB via Doctrine ORM
        $this->import($input, $output);

        $now = new \DateTime();
        $output->writeln('<comment>End : '.$now->format('d-m-Y G:i:s').' ---</comment>');
    }

    protected function import(InputInterface $input, OutputInterface $output)
    {
        // Getting php array of data from CSV
        $data = $this->get($input, $output);

        $em = $this->getContainer()->get('doctrine')->getManager();
        // Turning off doctrine default logs queries for saving memory
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        // Define the size of record, the frequency for persisting the data and the current index of records
        $size = count($data);
        $batchSize = 20;
        $i = 1;

        $user = $em->getRepository('WallabagUserBundle:User')
            ->findOneById($input->getArgument('userId'));

        if (!is_object($user)) {
            throw new Exception('User not found');
        }

        $progress = new ProgressBar($output, $size);
        $progress->start();

        foreach ($data as $object) {
            $array = (array) $object;
            $entry = $em->getRepository('WallabagCoreBundle:Entry')
                ->findOneByUrl($array['url']);

            if (!is_object($entry)) {
                $entry = new Entry($user);
                $entry->setUrl($array['url']);
            }

            $entry->setTitle($array['title']);
            $entry->setArchived($array['is_read']);
            $entry->setStarred($array['is_fav']);
            $entry->setContent($array['content']);
            $entry->setReadingTime(Utils::getReadingTime($array['content']));

            $em->persist($entry);

            if (($i % $batchSize) === 0) {
                $em->flush();
                $progress->advance($batchSize);

                $now = new \DateTime();
                $output->writeln(' of entries imported ... | '.$now->format('d-m-Y G:i:s'));
            }
            ++$i;
        }

        $em->flush();
        $em->clear();
        $progress->finish();
    }

    protected function convert($filename)
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        $header = null;
        $data = array();

        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgets($handle)) !== false) {
                $data = json_decode($row);
            }
            fclose($handle);
        }

        return $data;
    }

    protected function get(InputInterface $input, OutputInterface $output)
    {
        $filename = __DIR__.'/../../../../web/uploads/import/'.$input->getArgument('userId').'.json';

        $data = $this->convert($filename);

        return $data;
    }
}
