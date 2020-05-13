<?php

/*
 * This file is part of the Connect Holland Secure JWT package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\SecureJWT\EventSubscriber;

use ConnectHolland\SecureJWT\Entity\TwoFactorUserInterface;
use ConnectHolland\SecureJWT\Event\SetupTwoFactorAuthenticationEvent;
use Doctrine\Persistence\ManagerRegistry;
use Endroid\QrCode\Factory\QrCodeFactoryInterface;
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

    public static function getSubscribedEvents(): array
    {
        return [
            SetupTwoFactorAuthenticationEvent::NAME => 'provideQRCode',
        ];
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
