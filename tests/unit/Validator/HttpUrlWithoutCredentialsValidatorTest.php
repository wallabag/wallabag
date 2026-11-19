<?php

namespace Wallabag\Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Wallabag\Validator\HttpUrlWithoutCredentials;
use Wallabag\Validator\HttpUrlWithoutCredentialsValidator;

class HttpUrlWithoutCredentialsValidatorTest extends TestCase
{
    public function testRejectsAnUnrelatedConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        (new HttpUrlWithoutCredentialsValidator())->validate('https://example.org/feed', new NotBlank());
    }

    public function testRejectsANonStringValue(): void
    {
        $this->expectException(UnexpectedValueException::class);

        (new HttpUrlWithoutCredentialsValidator())->validate(123, new HttpUrlWithoutCredentials());
    }
}
