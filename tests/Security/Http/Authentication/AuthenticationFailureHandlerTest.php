<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Tests\Security\Http\Authentication;

use ConnectHolland\SecureJWTBundle\Event\SetupTwoFactorAuthenticationEvent;
use ConnectHolland\SecureJWTBundle\Exception\TwoFactorAuthenticationMissingException;
use ConnectHolland\SecureJWTBundle\Exception\TwoFactorSecretNotSetupException;
use ConnectHolland\SecureJWTBundle\Security\Http\Authentication\AuthenticationFailureHandler;
use ConnectHolland\SecureJWTBundle\Tests\Fixture\User;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class AuthenticationFailureHandlerTest extends TestCase
{
    public function testOnAuthenticationFailure2FANotSetup(): void
    {
        $dispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(SetupTwoFactorAuthenticationEvent::class),
                $this->equalTo(SetupTwoFactorAuthenticationEvent::NAME)
            );

        $failureHandler = new AuthenticationFailureHandler($this->createMock(AuthenticationFailureHandlerInterface::class), $dispatcher);
        $failureHandler->onAuthenticationFailure(new Request(), new TwoFactorSecretNotSetupException(new User()));
    }

    public function testOnAuthenticationFailureNoChallenge(): void
    {
        $request    = new Request();
        $dispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();

        $failureHandler = new AuthenticationFailureHandler($this->createMock(AuthenticationFailureHandlerInterface::class), $dispatcher);
        $response       = $failureHandler->onAuthenticationFailure($request, new TwoFactorAuthenticationMissingException());

        $content = json_decode($response->getContent(), true);
        $cookies = $response->headers->getCookies();

        $this->assertArrayHasKey('result', $content);
        $this->assertArrayHasKey('status', $content);
        $this->assertSame('two factor authentication required', $content['status']);
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertCount(0, $cookies);
    }

    public function testClearBearerCookieForInvalidToken(): void
    {
        $request   = new Request();
        $response  = new Response();
        $decorated = $this->createMock(AuthenticationFailureHandlerInterface::class);

        $response->headers->setCookie(new Cookie('BEARER', 'test'));
        $response->headers->setCookie(new Cookie('OTHER_COOKIE', 'test'));

        $decorated
            ->expects($this->once())
            ->method('onAuthenticationFailure')
            ->willReturn($response);

        $failureHandler = new AuthenticationFailureHandler($decorated, $this->createMock(EventDispatcher::class));

        $response = $failureHandler->onAuthenticationFailure($request, new InvalidTokenException('Invalid token found'));
        $cookies  = $response->headers->getCookies();

        $this->assertCount(2, $cookies);
        $this->assertSame('BEARER', $cookies[0]->getName());
        $this->assertSame(1, $cookies[0]->getExpiresTime(), 'BEARER Cookie should have an expire time in the past');
        $this->assertSame('OTHER_COOKIE', $cookies[1]->getName());
        $this->assertSame(0, $cookies[1]->getExpiresTime(), 'OTHER_COOKIE Cookie should not have an expire time');
    }
}
