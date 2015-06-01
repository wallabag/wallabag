<?php

namespace Wallabag\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ExecutionContextInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

class ForgotPasswordType extends AbstractType
{
    private $doctrine = null;

    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email', array(
                'required' => true,
                'constraints' => array(
                    new Constraints\Email(),
                    new Constraints\NotBlank(),
                    new Constraints\Callback(array(array($this, 'validateEmail'))),
                ),
            ))
        ;
    }

    public function getName()
    {
        return 'forgot_password';
    }

    public function validateEmail($email, ExecutionContextInterface $context)
    {
        $user = $this->doctrine
            ->getRepository('WallabagCoreBundle:User')
            ->findOneByEmail($email);

        if (!$user) {
            $context->addViolationAt(
                'email',
                'No user found with this email',
                array(),
                $email
            );
        }
    }
}
