<?php

namespace Wallabag\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Wallabag\CoreBundle\Form\Type\ResetPasswordType;

class SecurityController extends Controller
{
    public function oauthLoginAction(Request $request)
    {
        $session = $request->getSession();

        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        if ($error) {
            $error = $error->getMessage(
            ); // WARNING! Symfony source code identifies this line as a potential security threat.
        }

        $lastUsername = (null === $session) ? '' : $session->get(SecurityContext::LAST_USERNAME);

        return $this->render(
            'WallabagCoreBundle:Security:oauthlogin.html.twig',
            array(
                'last_username' => $lastUsername,
                'error' => $error,
            )
        );
    }


    public function loginAction(Request $request)
    {
        $session = $request->getSession();
        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        return $this->render('WallabagCoreBundle:Security:login.html.twig', array(
            // last username entered by the user
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
        ));
    }

    public function loginCheckAction(Request $request)
    {

    }

    /**
     * Request forgot password: show form.
     *
     * @Route("/forgot-password", name="forgot_password")
     *
     * @Method({"GET", "POST"})
     */
    public function forgotPasswordAction(Request $request)
    {
        $form = $this->createForm('forgot_password');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $this->getDoctrine()->getRepository('WallabagCoreBundle:User')->findOneByEmail($form->get('email')->getData());

            // generate "hard" token
            $user->setConfirmationToken(rtrim(strtr(base64_encode(hash('sha256', uniqid(mt_rand(), true), true)), '+/', '-_'), '='));
            $user->setPasswordRequestedAt(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $message = \Swift_Message::newInstance()
                ->setSubject('Reset Password')
                ->setFrom($this->container->getParameter('from_email'))
                ->setTo($user->getEmail())
                ->setBody($this->renderView('WallabagCoreBundle:Mail:forgotPassword.txt.twig', array(
                    'username' => $user->getUsername(),
                    'confirmationUrl' => $this->generateUrl('forgot_password_reset', array('token' => $user->getConfirmationToken()), true),
                )))
            ;
            $this->get('mailer')->send($message);

            return $this->redirect($this->generateUrl('forgot_password_check_email',
                array('email' => $this->getObfuscatedEmail($user->getEmail()))
            ));
        }

        return $this->render('WallabagCoreBundle:Security:forgotPassword.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Tell the user to check his email provider.
     *
     * @Route("/forgot-password/check-email", name="forgot_password_check_email")
     *
     * @Method({"GET"})
     */
    public function checkEmailAction(Request $request)
    {
        $email = $request->query->get('email');

        if (empty($email)) {
            // the user does not come from the forgotPassword action
            return $this->redirect($this->generateUrl('forgot_password'));
        }

        return $this->render('WallabagCoreBundle:Security:checkEmail.html.twig', array(
            'email' => $email,
        ));
    }

    /**
     * Reset user password.
     *
     * @Route("/forgot-password/{token}", name="forgot_password_reset")
     *
     * @Method({"GET", "POST"})
     */
    public function resetAction(Request $request, $token)
    {
        $user = $this->getDoctrine()->getRepository('WallabagCoreBundle:User')->findOneByConfirmationToken($token);

        if (null === $user) {
            throw $this->createNotFoundException(sprintf('No user found with token "%s"', $token));
        }

        $form = $this->createForm(new ResetPasswordType());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user->setPassword($form->get('new_password')->getData());

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'The password has been reset successfully'
            );

            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render('WallabagCoreBundle:Security:reset.html.twig', array(
            'token' => $token,
            'form' => $form->createView(),
        ));
    }

    /**
     * Get the truncated email displayed when requesting the resetting.
     *
     * Keeping only the part following @ in the address.
     *
     * @param string $email
     *
     * @return string
     */
    protected function getObfuscatedEmail($email)
    {
        if (false !== $pos = strpos($email, '@')) {
            $email = '...'.substr($email, $pos);
        }

        return $email;
    }
}
