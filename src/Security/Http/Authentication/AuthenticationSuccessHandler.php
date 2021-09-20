<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Security\Http\Authentication;

use ConnectHolland\SecureJWTBundle\Entity\InvalidToken;
use ConnectHolland\SecureJWTBundle\Entity\RememberDeviceToken;
use ConnectHolland\SecureJWTBundle\Resolver\RememberDeviceResolver;
use Doctrine\Common\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

/**
 * @deprecated Use lexik default instead
 **/
class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private AuthenticationSuccessHandlerInterface $successHandler;

    private JWTEncoderInterface $jwtEncoder;

    private RememberDeviceResolver $rememberDeviceResolver;

    private array $responsePayload = [];

    private string $sameSite;

    private ManagerRegistry $doctrine;

    public function __construct(AuthenticationSuccessHandlerInterface $successHandler, JWTEncoderInterface $jwtEncoder, string $sameSite, RememberDeviceResolver $rememberDeviceResolver, ManagerRegistry $doctrine)
    {
        $this->rememberDeviceResolver = $rememberDeviceResolver;
        $this->successHandler         = $successHandler;
        $this->jwtEncoder             = $jwtEncoder;
        $this->sameSite               = $sameSite;
        $this->doctrine               = $doctrine;
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
        $response->headers->setCookie(new Cookie('BEARER', $data['token'], $decoded['exp'], '/', null, true, true, false, $this->sameSite));

        return $response;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        $response = $this->handleAuthenticationSuccess($token->getUser());

        if ($this->rememberDeviceResolver->getRememberDeviceStatus()) {
            if (is_null($request->cookies) || is_null($request->cookies->get('REMEMBER_DEVICE')) || $this->jwtEncoder->decode($request->cookies->get('REMEMBER_DEVICE'))['exp'] < time()) {
                // Only add the token to the invalid tokens table if the expiry time is invalid, not if the cookie is null
                if (!is_null($request->cookies) && !is_null($request->cookies->get('REMEMBER_DEVICE'))) {
                    $this->addToInvalidTokens($request->cookies->get('REMEMBER_DEVICE'));
                }

                $expiry_time = time() + $this->rememberDeviceResolver->getRememberDeviceExpiryDays() * 86400;
                $username    = $request->request->get('username');

                $data = $this->jwtEncoder->encode([
                    'exp'  => $expiry_time,
                    'user' => $username,
                ]);

                $this->addToValidTokens($data, $username);

                $response->headers->setCookie(new Cookie('REMEMBER_DEVICE', $data, $expiry_time, '/', null, true, false, $this->sameSite));
            }
        }

        return $response;
    }

    /**
     * Add fields to the response payload.
     */
    public function addResponsePayload(string $key, $value): void
    {
        $this->responsePayload[$key] = $value;
    }

    private function addToInvalidTokens($token): void
    {
        $entityManager = $this->doctrine->getManager();

        $invalidToken = new InvalidToken();
        $invalidToken->setToken($token);
        $invalidToken->setInvalidatedAt(new \DateTime('now'));

        if (!is_null($entityManager)) {
            $entityManager->persist($invalidToken);
            $entityManager->flush();
        }
    }

    private function addToValidTokens($token, $user): void
    {
        $entityManager = $this->doctrine->getManager();

        $rememberDeviceToken = new RememberDeviceToken();
        $rememberDeviceToken->setToken($token);
        $rememberDeviceToken->setUsername($user);

        if (!is_null($entityManager)) {
            $entityManager->persist($rememberDeviceToken);
            $entityManager->flush();
        }
    }
}
