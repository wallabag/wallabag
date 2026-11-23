<?php

namespace Wallabag\DependencyInjection;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

class NotEmptyEnvVarProcessor implements EnvVarProcessorInterface
{
    public function getEnv(string $prefix, string $name, \Closure $getEnv): mixed
    {
        $value = $getEnv($name);

        if (null === $value || '' === $value) {
            throw new RuntimeException(\sprintf('Environment variable "%s" must not be empty.', $name));
        }

        return $value;
    }

    public static function getProvidedTypes(): array
    {
        return ['not_empty' => 'string'];
    }
}
