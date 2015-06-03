<?php

namespace Wallabag\CoreBundle\Security\Validator;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

/**
 * @see Symfony\Component\Security\Core\Validator\Constraints\UserPasswordValidator
 */
class WallabagUserPasswordValidator extends ConstraintValidator
{
    private $securityContext;
    private $encoderFactory;

    public function __construct(TokenStorageInterface $tokenStorage, EncoderFactoryInterface $encoderFactory)
    {
        $this->tokenStorage = $tokenStorage;
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($password, Constraint $constraint)
    {
        if (!$constraint instanceof UserPassword) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\UserPassword');
        }

        $user = $this->tokenStorage->getToken()->getUser();

        if (!$user instanceof UserInterface) {
            throw new ConstraintDefinitionException('The User object must implement the UserInterface interface.');
        }

        // give username, it's used to hash the password
        $encoder = $this->encoderFactory->getEncoder($user);
        $encoder->setUsername($user->getUsername());

        if (!$encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
            $this->context->addViolation($constraint->message);
        }
    }
}
