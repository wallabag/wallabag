<?php

namespace Wallabag\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Wallabag\Helper\FeedUrlValidator;

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

        if (!FeedUrlValidator::isValid($value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
