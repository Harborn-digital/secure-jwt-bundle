<?php

declare(strict_types=1);

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Security\Guard;

use ConnectHolland\SecureJWTBundle\Entity\InvalidToken;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator as BaseAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\Translation\TranslatorInterface;


class JWTTokenAuthenticator extends BaseAuthenticator
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine, JWTTokenManagerInterface $jwtManager, EventDispatcherInterface $dispatcher, TokenExtractorInterface $tokenExtractor, TokenStorageInterface $tokenStorage)
    {
        parent::__construct($jwtManager, $dispatcher, $tokenExtractor, $tokenStorage);

        $this->doctrine = $doctrine;
    }

    /**
     * Add token validation to guard.
     */
    public function getCredentials(Request $request)
    {
        $token      = $this->getTokenExtractor()->extract($request);
        $repository = $this->doctrine->getRepository(InvalidToken::class);

        if (!$repository instanceof EntityRepository) {
            throw new \RuntimeException('Unable to verify token because doctrine is not set up correctly. Please configure `vendor/connectholland/secure-jwt/src/Entity` as an annotated entity path (see README.md for more details)');
        }

        if ($repository->findOneBy(['token' => $token]) instanceof InvalidToken) {
            throw new InvalidTokenException('Invalidated JWT Token');
        }

        return parent::getCredentials($request);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $authException): Response
    {
        $response = parent::onAuthenticationFailure($request, $authException); // TODO: Change the autogenerated stub

        if ($authException instanceof InvalidTokenException || $authException instanceof ExpiredTokenException) {
            $response->headers->clearCookie('BEARER', '/', null, true, true, 'none');
        }

        return $response;
    }
}
