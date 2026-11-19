<?php

namespace Wallabag\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class HttpUrlWithoutCredentialsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof HttpUrlWithoutCredentials) {
            throw new UnexpectedTypeException($constraint, HttpUrlWithoutCredentials::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!$this->isValid($value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }

    private function isValid(string $url): bool
    {
        if (!filter_var($url, \FILTER_VALIDATE_URL)) {
            return false;
        }

        $parts = parse_url($url);

        return \in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            && !isset($parts['user'])
            && !isset($parts['pass']);
    }
}
