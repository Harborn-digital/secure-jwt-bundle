<?php

/*
 * This file is part of the Connect Holland Secure JWT package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Tests\Security\Http\Authentication;

use ConnectHolland\SecureJWTBundle\Event\SetupTwoFactorAuthenticationEvent;
use ConnectHolland\SecureJWTBundle\Exception\TwoFactorAuthenticationMissingException;
use ConnectHolland\SecureJWTBundle\Exception\TwoFactorSecretNotSetupException;
use ConnectHolland\SecureJWTBundle\Security\Http\Authentication\AuthenticationFailureHandler;
use ConnectHolland\SecureJWTBundle\Tests\Fixture\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

        $failureHandler = new AuthenticationFailureHandler($dispatcher);
        $failureHandler->onAuthenticationFailure(new Request(), new TwoFactorSecretNotSetupException(new User()));
    }

    public function testOnAuthenticationFailureNoChallenge(): void
    {
        $request    = new Request();
        $dispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();

        $failureHandler = new AuthenticationFailureHandler($dispatcher);
        $response       = $failureHandler->onAuthenticationFailure($request, new TwoFactorAuthenticationMissingException());

        $content = json_decode($response->getContent(), true);
        $cookies = $response->headers->getCookies();

        $this->assertArrayHasKey('result', $content);
        $this->assertArrayHasKey('status', $content);
        $this->assertSame('two factor authentication required', $content['status']);
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertCount(0, $cookies);
    }
}
