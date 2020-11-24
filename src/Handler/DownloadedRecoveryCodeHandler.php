<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Handler;

use ConnectHolland\SecureJWTBundle\Entity\RecoveryCode;
use ConnectHolland\SecureJWTBundle\Entity\TwoFactorUserInterface;
use ConnectHolland\SecureJWTBundle\Message\DownloadedRecoveryCode;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DownloadedRecoveryCodeHandler implements MessageHandlerInterface
{
    private ManagerRegistry $doctrine;

    private TokenStorageInterface $tokenStorage;

    /**
     * RecoveryCodeHandler constructor.
     */
    public function __construct(ManagerRegistry $doctrine, TokenStorageInterface $tokenStorage)
    {
        $this->doctrine     = $doctrine;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Set downloaded to true on recovery code(s) for logged on user.
     */
    public function __invoke(DownloadedRecoveryCode $recoveryCode): void
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user instanceof TwoFactorUserInterface) {
            $this->setDownloaded($user->getGoogleAuthenticatorSecret(), $recoveryCode->getValue());
        } else {
            throw new \RuntimeException('Unable to set downloaded on recovery codes for non-2fa users');
        }
    }

    /**
     * Set downloaded to true on recovery code(s) for the given User.
     */
    private function setDownloaded(string $secret, bool $value): void
    {
        $currentCodes = $this->doctrine->getRepository(RecoveryCode::class)->findBy(['secret' => $secret]);
        $manager      = $this->doctrine->getManagerForClass(RecoveryCode::class);

        array_walk($currentCodes, fn (RecoveryCode $recoveryCode) => $recoveryCode->setDownloaded($value));

        $manager->flush();
    }
}
