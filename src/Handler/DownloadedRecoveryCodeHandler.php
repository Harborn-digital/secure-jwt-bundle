<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Handler;

use ConnectHolland\SecureJWTBundle\Entity\RecoveryCode as RecoveryCodeEntity;
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
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Invalidate codes for logged on user and create new codes.
     */
    public function __invoke(DownloadedRecoveryCode $recoveryCode): void
    {
        $user = $this->doctrine->getRepository(RecoveryCodeEntity::class)->findBy(['secret' => $recoveryCode->getValue()]);

        if ($user instanceof TwoFactorUserInterface) {
            $this->setDownloaded($user->getGoogleAuthenticatorSecret());
        }

        throw new \RuntimeException('Unable to create recovery codes for non-2fa users');
    }

    /**
     * Set downloaded to true on recovery code(s) of current User.
     */
    private function setDownloaded(string $secret): void
    {
        $currentCodes = $this->doctrine->getRepository(RecoveryCodeEntity::class)->findBy(['secret' => $secret]);
        foreach($currentCodes as $code) {
            $code->setDownloaded(true);
        }
        $manager = $this->doctrine->getManagerForClass(RecoveryCodeEntity::class);
        $manager->flush();
    }
}
