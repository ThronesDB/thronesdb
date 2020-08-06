<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @package App\Controller
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/oauth/v2/auth_login", name="oauth_server_auth_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function loginAction(AuthenticationUtils $authenticationUtils)
    {
        return $this->render(
            'Security/login.html.twig',
            [
                'last_username' => $authenticationUtils->getLastUsername(),
                'error'         => $authenticationUtils->getLastAuthenticationError(),
            ]
        );
    }

    /**
     * @Route("/oauth/v2/auth_login_check", name="oauth_server_auth_login_check")
     */
    public function loginCheckAction()
    {
    }
}
