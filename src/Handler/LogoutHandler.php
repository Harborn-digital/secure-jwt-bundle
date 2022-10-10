<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Handler;

use ConnectHolland\SecureJWTBundle\Entity\InvalidToken;
use ConnectHolland\SecureJWTBundle\Message\Logout;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LogoutHandler implements MessageHandlerInterface
{
    private TokenStorageInterface $tokenStorage;

    private ManagerRegistry $doctrine;

    public function __construct(TokenStorageInterface $tokenStorage, ManagerRegistry $doctrine)
    {
        $this->tokenStorage = $tokenStorage;
        $this->doctrine     = $doctrine;
    }

    /**
     * Invalidate the current login by invalidating the current JWT token.
     */
    public function __invoke(Logout $logout): Response
    {
        $token    = $this->tokenStorage->getToken();
        $response = new Response();

        if ($token instanceof JWTUserToken) {
            $invalidToken = new InvalidToken();
            $invalidToken->setToken($token->getCredentials());
            $invalidToken->setInvalidatedAt(new \DateTime());

            $manager = $this->doctrine->getManagerForClass(InvalidToken::class);
            if ($manager instanceof EntityManager) {
                $manager->persist($invalidToken);
                $manager->flush();
            } else {
                throw new \RuntimeException('Unable to invalid token because doctrine is not set up correctly. Please configure `vendor/connectholland/secure-jwt/src/Entity` as an annotated entity path (see README.md for more details)');
            }

            $response->headers->clearCookie('BEARER', '/', null, true, true, 'none');
        }

        return $response;
    }
}
