<?php

/*
 * This file is part of the Connect Holland Secure JWT package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private AuthenticationSuccessHandlerInterface $successHandler;

    public function __construct(AuthenticationSuccessHandlerInterface $successHandler)
    {
        $this->successHandler = $successHandler;
    }

    /**
     * Set the JWT token as a Cookie.
     */
    public function handleAuthenticationSuccess(UserInterface $user, $jwt = null): JsonResponse
    {
        $jsonWithToken = $this->successHandler->handleAuthenticationSuccess($user, $jwt);
        $response      = new JsonResponse(['result' => 'ok']);
        $data          = json_decode($jsonWithToken->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $response->headers->setCookie(new Cookie('BEARER', $data['token'], 0, '/', null, true));

        return  $response;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        return $this->handleAuthenticationSuccess($token->getUser());
    }
}
