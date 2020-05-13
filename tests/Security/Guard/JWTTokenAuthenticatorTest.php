<?php

/*
 * This file is part of the Connect Holland Secure JWT package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\SecureJWT\Tests\Security\Guard;

use ConnectHolland\SecureJWT\Entity\InvalidToken;
use ConnectHolland\SecureJWT\Security\Guard\JWTTokenAuthenticator;
use Doctrine\ORM\EntityRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\QueryParameterTokenExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

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
