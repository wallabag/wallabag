<?php

namespace Wallabag\CoreBundle\Security\Validator;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class WallabagUserPasswordValidator extends ConstraintValidator
{
    private $securityContext;
    private $encoderFactory;

    public function __construct(SecurityContextInterface $securityContext, EncoderFactoryInterface $encoderFactory)
    {
        $this->securityContext = $securityContext;
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

        $user = $this->securityContext->getToken()->getUser();

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
