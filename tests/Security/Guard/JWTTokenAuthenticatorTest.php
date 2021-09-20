<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Tests\Security\Guard;

use ConnectHolland\SecureJWTBundle\Entity\InvalidToken;
use ConnectHolland\SecureJWTBundle\Security\Guard\JWTTokenAuthenticator;
use Doctrine\ORM\EntityRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\QueryParameterTokenExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class JWTTokenAuthenticatorTest extends TestCase
{
    public function testInvalidatedTokenCausesException(): void
    {
        $this->expectException(InvalidTokenException::class);
        $this->expectExceptionMessage('Invalidated JWT Token');

        $request               = new Request(['unit-test' => 'invalid']);
        $jwtTokenAuthenticator = $this->getAuthenticator(new InvalidToken());
        $jwtTokenAuthenticator->getCredentials($request);
    }

    public function testValidTokensArePassedOn(): void
    {
        $this->expectException(InvalidTokenException::class);
        $this->expectExceptionMessage('Invalid JWT Token');

        $request               = new Request(['unit-test' => 'invalid']);
        $jwtTokenAuthenticator = $this->getAuthenticator(null);
        $jwtTokenAuthenticator->getCredentials($request);
    }

    public function testExceptionOnDoctrineIssue(): void
    {
        $this->expectException(\RuntimeException::class);

        $request               = new Request(['unit-test' => 'invalid']);
        $jwtTokenAuthenticator = $this->getAuthenticator(null, false);
        $jwtTokenAuthenticator->getCredentials($request);
    }

    /**
     * @dataProvider provideTestExceptions
     */
    public function testBearerCookieIsCleared(AuthenticationException $exception, bool $clearCookie): void
    {
        $request       = new Request(['unit-test' => 'invalid']);
        $authenticator = $this->getAuthenticator(null, false);
        $response      = $authenticator->onAuthenticationFailure($request, $exception);

        if ($clearCookie) {
            $this->assertCount(1, $response->headers->getCookies());
        } else {
            $this->assertCount(0, $response->headers->getCookies());
        }
    }

    public function provideTestExceptions(): array
    {
        return [
            [new AuthenticationException('Root exception'), false],
            [new InvalidTokenException('Invalid token'), true],
            [new ExpiredTokenException('Expired token'), true],
        ];
    }

    private function getAuthenticator(?InvalidToken $invalidToken, bool $setupRepository = true): JWTTokenAuthenticator
    {
        $tokenExtractor = new QueryParameterTokenExtractor('unit-test');

        $doctrine   = $this->createMock(ManagerRegistry::class);
        $repository = $this->createMock(EntityRepository::class);

        if ($setupRepository) {
            $repository
                ->expects($this->once())
                ->method('findOneBy')
                ->willReturn($invalidToken);

            $doctrine
                ->expects($this->once())
                ->method('getRepository')
                ->willReturn($repository);
        }

        return new JWTTokenAuthenticator($doctrine, $this->createMock(JWTManager::class), $this->createMock(EventDispatcherInterface::class), $tokenExtractor);
    }
}
