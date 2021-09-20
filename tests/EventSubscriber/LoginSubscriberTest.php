<?php

declare(strict_types=1);

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Tests\EventSubscriber;

use ConnectHolland\SecureJWTBundle\Event\SetupTwoFactorAuthenticationEvent;
use ConnectHolland\SecureJWTBundle\EventSubscriber\LoginSubscriber;
use ConnectHolland\SecureJWTBundle\Tests\Fixture\User;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Endroid\QrCode\Factory\QrCodeFactoryInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class LoginSubscriberTest extends TestCase
{
    private ManagerRegistry $doctrine;

    private MockObject $qrCodeFactory;

    private MockObject $router;

    private MockObject $googleAuthenticator;

    private MockObject $dispatcher;

    private MockObject $translator;

    private LoginSubscriber $loginSubscriber;

    /**
     * Setup variables.
     */
    public function setUp(): void
    {
        $this->doctrine            = $this->createMock(ManagerRegistry::class);
        $this->qrCodeFactory       = $this->createMock(QrCodeFactoryInterface::class);
        $this->googleAuthenticator = $this->createMock(GoogleAuthenticator::class);

        $this->loginSubscriber = new LoginSubscriber($this->doctrine, $this->qrCodeFactory, $this->googleAuthenticator);
    }

    public function testConfirm2FASecret(): void
    {
        $event   = new AuthenticationSuccessEvent([], new User(), new Response());
        $manager = $this->createMock(EntityManager::class);

        $manager
            ->expects($this->once())
            ->method('flush');

        $this->doctrine
            ->expects($this->once())
            ->method('getManager')
            ->willReturn($manager);

        $this->loginSubscriber->confirm2Fa($event);

        $this->assertTrue($event->getUser()->isGoogleAuthenticatorConfirmed());
    }

    public function testProvideQRCode(): void
    {
        $manager = $this->createMock(EntityManager::class);

        $manager
            ->expects($this->once())
            ->method('flush');

        $this->doctrine
            ->expects($this->once())
            ->method('getManager')
            ->willReturn($manager);

        $event = new SetupTwoFactorAuthenticationEvent(new User());
        $this->loginSubscriber->provideQRCode($event);

        $response = $event->getResponse();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('result', $content);
        $this->assertArrayHasKey('message', $content);
        $this->assertArrayHasKey('qr', $content);
    }
}
