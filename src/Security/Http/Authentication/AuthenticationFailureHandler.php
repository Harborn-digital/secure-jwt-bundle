<?php

/*
 * This file is part of the Connect Holland Secure JWT package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\SecureJWT\Security\Http\Authentication;

use ConnectHolland\SecureJWT\Event\SetupTwoFactorAuthenticationEvent;
use ConnectHolland\SecureJWT\Exception\TwoFactorAuthenticationMissingException;
use ConnectHolland\SecureJWT\Exception\TwoFactorSecretNotSetupException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationFailureHandler as LexikAuthenticationFailureHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthenticationFailureHandler extends LexikAuthenticationFailureHandler
{
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

        // Ignore coverage because this is just calling the parent
        return parent::onAuthenticationFailure($request, $exception); // @codeCoverageIgnore
    }
}
