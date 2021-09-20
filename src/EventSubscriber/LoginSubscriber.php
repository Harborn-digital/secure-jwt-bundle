<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\EventSubscriber;

use ConnectHolland\SecureJWTBundle\Entity\TwoFactorUserInterface;
use ConnectHolland\SecureJWTBundle\Event\SetupTwoFactorAuthenticationEvent;
use Doctrine\Persistence\ManagerRegistry;
use Endroid\QrCode\Factory\QrCodeFactoryInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class LoginSubscriber implements EventSubscriberInterface
{
    private ManagerRegistry $doctrine;

    private QrCodeFactoryInterface $qrCodeFactory;

    private GoogleAuthenticator $googleAuthenticator;

    public function __construct(ManagerRegistry $doctrine, QrCodeFactoryInterface $qrCodeFactory, GoogleAuthenticator $googleAuthenticator)
    {
        $this->doctrine            = $doctrine;
        $this->qrCodeFactory       = $qrCodeFactory;
        $this->googleAuthenticator = $googleAuthenticator;
    }

    /**
     * @codeCoverageIgnore Trivial getter
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SetupTwoFactorAuthenticationEvent::NAME => 'provideQRCode',
            Events::AUTHENTICATION_SUCCESS          => 'confirm2Fa',
        ];
    }

    public function confirm2Fa(AuthenticationSuccessEvent $event): void
    {
        /** @var TwoFactorUserInterface $user */
        $user = $event->getUser();
        if (false === $user->isGoogleAuthenticatorConfirmed()) {
            $user->setGoogleAuthenticatorConfirmed(true);
            $this->doctrine->getManager()->flush();
        }
    }

    public function provideQRCode(SetupTwoFactorAuthenticationEvent $event): void
    {
        $this->setGoogleAuthenticatorSecret($event->getUser());

        $qrContent = $this->googleAuthenticator->getQRContent($event->getUser());
        $qrCode    = $this->qrCodeFactory->create($qrContent, ['size' => 150]);

        $event->setResponse(
            new JsonResponse(
                [
                    'result'  => 'ok',
                    'message' => 'use provided QR code to set up two factor authentication',
                    'qr'      => $qrCode->writeDataUri(),
                ],
                Response::HTTP_OK
            )
        );
    }

    private function setGoogleAuthenticatorSecret(TwoFactorUserInterface $user): void
    {
        $secret = $this->googleAuthenticator->generateSecret();
        $user->setGoogleAuthenticatorSecret($secret);

        $entityManager = $this->doctrine->getManager();
        $entityManager->flush();
    }
}
