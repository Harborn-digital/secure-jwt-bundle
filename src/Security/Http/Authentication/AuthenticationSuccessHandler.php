<?php

/*
 * This file is part of the Connect Holland Secure JWT package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Security\Http\Authentication;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private AuthenticationSuccessHandlerInterface $successHandler;

    private JWTEncoderInterface $jwtEncoder;

    private array $responsePayload = [];

    public function __construct(AuthenticationSuccessHandlerInterface $successHandler, JWTEncoderInterface $jwtEncoder)
    {
        $this->successHandler = $successHandler;
        $this->jwtEncoder     = $jwtEncoder;
    }

    /**
     * Set the JWT token as a Cookie.
     */
    public function handleAuthenticationSuccess(UserInterface $user, $jwt = null): JsonResponse
    {
        $jsonWithToken         = $this->successHandler->handleAuthenticationSuccess($user, $jwt);
        $data                  = json_decode($jsonWithToken->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $decoded               = $this->jwtEncoder->decode($data['token']);
        $this->responsePayload = array_merge($this->responsePayload, $decoded);
        $response              = new JsonResponse(['result' => 'ok', 'payload' => $this->responsePayload]);
        $response->headers->setCookie(new Cookie('BEARER', $data['token'], 0, '/', null, true));

        return  $response;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        return $this->handleAuthenticationSuccess($token->getUser());
    }

    /**
     * Add fields to the response payload. 
     */
    public function addResponsePayload(string $key, string $value): void
    {
        $this->responsePayload[$key] = $value;
    }
}
