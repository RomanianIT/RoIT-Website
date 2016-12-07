<?php

namespace WebsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     * @Template
     */
    public function loginAction(Request $request)
    {
        $user = $this->getUser();
        if ($user instanceof UserInterface) {
            return $this->redirectToRoute('website_homepage');
        }

        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        return  array(
            // last username entered by the user
            'last_username' => $lastUsername,
            'error'         => $error,
        );
//        return $this->render(
//            'security/login.html.twig',
//            array(
//                // last username entered by the user
//                'last_username' => $lastUsername,
//                'error'         => $error,
//            )
//        );
    }
}