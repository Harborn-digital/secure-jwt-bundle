<?php

/*
 * This file is part of the Connect Holland Secure JWT package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Security\Http\Authentication;

use ConnectHolland\SecureJWTBundle\Event\SetupTwoFactorAuthenticationEvent;
use ConnectHolland\SecureJWTBundle\Exception\TwoFactorAuthenticationMissingException;
use ConnectHolland\SecureJWTBundle\Exception\TwoFactorSecretNotSetupException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    private AuthenticationFailureHandlerInterface $lexikAuthenticationFailureHandler;

    private EventDispatcherInterface $dispatcher;

    public function __construct(AuthenticationFailureHandlerInterface $lexikAuthenticationFailureHandler, EventDispatcherInterface $dispatcher)
    {
        $this->lexikAuthenticationFailureHandler = $lexikAuthenticationFailureHandler;
        $this->dispatcher                        = $dispatcher;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        if ($exception instanceof TwoFactorSecretNotSetupException) {
            $event = new SetupTwoFactorAuthenticationEvent($exception->getUser());
            $this->dispatcher->dispatch($event, SetupTwoFactorAuthenticationEvent::NAME);

            return $event->getResponse();
        }

        if ($exception instanceof TwoFactorAuthenticationMissingException) {
            return new JsonResponse(['result' => 'ok', 'status' => 'two factor authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        // Ignore coverage because this is just calling the decorated service
        $response = $this->lexikAuthenticationFailureHandler->onAuthenticationFailure($request, $exception); // @codeCoverageIgnore

        if ($exception instanceof InvalidTokenException) {
            $response->headers->clearCookie('BEARER');
        }

        return $response;
    }
}
