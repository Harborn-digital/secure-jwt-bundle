<?php

/*
 * This file is part of the Connect Holland Secure JWT package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\SecureJWT\Security\Http\Authentication;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler as LexikAuthenticationSuccessHandler;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationSuccessHandler extends LexikAuthenticationSuccessHandler
{
    /**
     * Set the JWT token as a Cookie.
     */
    public function handleAuthenticationSuccess(UserInterface $user, $jwt = null): JsonResponse
    {
        $jsonWithToken = parent::handleAuthenticationSuccess($user, $jwt);
        $response      = new JsonResponse(['result' => 'ok']);
        $data          = json_decode($jsonWithToken->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $response->headers->setCookie(new Cookie('BEARER', $data['token'], 0, '/', null, true));

        return  $response;
    }
}
