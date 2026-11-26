<?php

namespace Wallabag\Twig;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Wallabag\Helper\UpdateChecker;

class UpdateExtension extends AbstractExtension
{
    public function __construct(
        private readonly UpdateChecker $updateChecker,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('wallabag_available_update', $this->getAvailableUpdate(...)),
        ];
    }

    /**
     * @return array{version: string, security: bool, url: string}|null
     */
    public function getAvailableUpdate(): ?array
    {
        if (!$this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            return null;
        }

        return $this->updateChecker->getAvailableUpdate();
    }
}
