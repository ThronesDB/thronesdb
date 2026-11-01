<?php

namespace App\EventListener;

use League\Bundle\OAuth2ServerBundle\Event\AuthorizationRequestResolveEvent;
use League\Bundle\OAuth2ServerBundle\OAuth2Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;

/**
 * Asks the logged-in user whether to let an OAuth2 client act on their
 * account, before the authorization server issues a code.
 *
 * The first request to /oauth/v2/authorize shows a consent page; the page
 * posts back to the same URL with the user's decision.
 */
#[AsEventListener(event: OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE)]
final class OAuthAuthorizationListener
{
    private const CSRF_ID = 'oauth2_consent';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Environment $twig,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    public function __invoke(AuthorizationRequestResolveEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }

        $token = new CsrfToken(self::CSRF_ID, (string) $request->request->get('_token'));
        if ($request->isMethod('POST') && $this->csrfTokenManager->isTokenValid($token)) {
            $event->resolveAuthorization('approve' === $request->request->get('decision'));

            return;
        }

        $event->setResponse(new Response($this->twig->render('OAuth/consent.html.twig', [
            'client' => $event->getClient(),
            'scopes' => array_map(fn ($scope) => (string) $scope, $event->getScopes()),
            'action' => $request->getRequestUri(),
            'csrf_id' => self::CSRF_ID,
        ])));
    }
}
