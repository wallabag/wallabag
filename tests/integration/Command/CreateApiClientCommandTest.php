<?php

namespace Wallabag\Tests\Integration\Command;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class CreateApiClientCommandTest extends WallabagKernelTestCase
{
    public function testMissingUsername(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments');

        $application = $this->createApplication();

        $command = $application->find('wallabag:api-client:create');

        $tester = new CommandTester($command);
        $tester->execute([]);
    }

    public function testUnknownUser(): void
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:api-client:create');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'unknown',
        ]);

        $this->assertStringContainsString('User "unknown" not found', $tester->getDisplay());
        $this->assertSame(1, $tester->getStatusCode());
    }

    public function testCreateClientDefaultFormat(): void
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:api-client:create');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
        ]);

        $this->assertStringContainsString('client_id', $tester->getDisplay());
        $this->assertStringContainsString('client_secret', $tester->getDisplay());
        $this->assertSame(0, $tester->getStatusCode());
    }

    public function testCreateClientEnvFormat(): void
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:api-client:create');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
            '--format' => 'env',
        ]);

        $this->assertStringContainsString('WALLABAG_CLIENT_ID="', $tester->getDisplay());
        $this->assertStringContainsString('WALLABAG_CLIENT_SECRET="', $tester->getDisplay());
        $this->assertSame(0, $tester->getStatusCode());
    }

    public function testCreateClientJsonFormat(): void
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:api-client:create');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
            '--format' => 'json',
        ]);

        $decoded = json_decode($tester->getDisplay(), true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('client_id', $decoded);
        $this->assertArrayHasKey('client_secret', $decoded);
        $this->assertArrayHasKey('name', $decoded);
        $this->assertSame(0, $tester->getStatusCode());
    }

    public function testCreateClientWithCustomDisplayName(): void
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:api-client:create');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
            '--display-name' => 'my-custom-client',
        ]);

        $this->assertStringContainsString('my-custom-client', $tester->getDisplay());
        $this->assertSame(0, $tester->getStatusCode());
    }

    public function testCreateClientWithCustomGrantTypes(): void
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:api-client:create');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
            '--grant-types' => 'password,refresh_token',
            '--format' => 'json',
        ]);

        $this->assertSame(0, $tester->getStatusCode());
        $decoded = json_decode($tester->getDisplay(), true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('client_id', $decoded);
    }

    public function testInvalidFormat(): void
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:api-client:create');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
            '--format' => 'xml',
        ]);

        $this->assertStringContainsString('Unknown format "xml"', $tester->getDisplay());
        $this->assertSame(1, $tester->getStatusCode());
    }

    public function testInvalidGrantType(): void
    {
        $application = $this->createApplication();

        $command = $application->find('wallabag:api-client:create');

        $tester = new CommandTester($command);
        $tester->execute([
            'username' => 'admin',
            '--grant-types' => 'password,client_credentials',
        ]);

        $this->assertStringContainsString('Invalid grant type(s)', $tester->getDisplay());
        $this->assertStringContainsString('client_credentials', $tester->getDisplay());
        $this->assertSame(1, $tester->getStatusCode());
    }
}
