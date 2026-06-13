<?php

namespace Wallabag\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wallabag\Entity\Api\Client;
use Wallabag\Repository\UserRepository;

class CreateApiClientCommand extends Command
{
    private const ALLOWED_GRANT_TYPES = ['token', 'authorization_code', 'password', 'refresh_token'];
    protected static $defaultName = 'wallabag:api-client:create';
    protected static $defaultDescription = 'Create an OAuth API client for an existing user';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command creates an OAuth API client for an existing user')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'User to create a client for'
            )
            ->addOption(
                'display-name',
                null,
                InputOption::VALUE_REQUIRED,
                'Display name of the client',
                'Default client'
            )
            ->addOption(
                'grant-types',
                null,
                InputOption::VALUE_REQUIRED,
                'Comma-separated list of allowed grant types (' . implode(', ', self::ALLOWED_GRANT_TYPES) . ')',
                implode(',', self::ALLOWED_GRANT_TYPES)
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_REQUIRED,
                'Output format (text, env, json)',
                'text'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = $input->getArgument('username');

        try {
            $user = $this->userRepository->findOneByUserName($username);
        } catch (NoResultException) {
            $io->error(\sprintf('User "%s" not found.', $username));

            return 1;
        }

        $format = (string) $input->getOption('format');
        if (!\in_array($format, ['text', 'env', 'json'], true)) {
            $io->error(\sprintf('Unknown format "%s". Use text, env, or json.', $format));

            return 1;
        }

        $grantTypes = array_filter(array_map('trim', explode(',', (string) $input->getOption('grant-types'))));
        $invalidGrantTypes = array_diff($grantTypes, self::ALLOWED_GRANT_TYPES);
        if (!empty($invalidGrantTypes)) {
            $io->error(\sprintf(
                'Invalid grant type(s): %s. Allowed values are: %s.',
                implode(', ', $invalidGrantTypes),
                implode(', ', self::ALLOWED_GRANT_TYPES)
            ));

            return 1;
        }

        $client = new Client($user);
        $client->setName((string) $input->getOption('display-name'));
        $client->setAllowedGrantTypes(array_values($grantTypes));

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        switch ($format) {
            case 'env':
                $output->writeln('WALLABAG_CLIENT_ID="' . $client->getPublicId() . '"');
                $output->writeln('WALLABAG_CLIENT_SECRET="' . $client->getSecret() . '"');
                break;
            case 'json':
                $output->writeln((string) json_encode([
                    'client_id' => $client->getPublicId(),
                    'client_secret' => $client->getSecret(),
                    'name' => $client->getName(),
                ], \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR));
                break;
            default:
                $io->success('Client created');
                $io->definitionList(
                    ['client_id' => $client->getPublicId()],
                    ['client_secret' => $client->getSecret()],
                    ['name' => $client->getName()],
                );
        }

        return 0;
    }
}
