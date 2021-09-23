<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Handler;

use ConnectHolland\SecureJWTBundle\DTO\GeneratedCodes;
use ConnectHolland\SecureJWTBundle\Entity\RecoveryCode as RecoveryCodeEntity;
use ConnectHolland\SecureJWTBundle\Entity\TwoFactorUserInterface;
use ConnectHolland\SecureJWTBundle\Message\RecoveryCode;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RecoveryCodeHandler implements MessageHandlerInterface
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
     * Invalidate codes for logged on user and create new codes.
     */
    public function __invoke(RecoveryCode $recoveryCode): GeneratedCodes
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user instanceof TwoFactorUserInterface) {
            $this->invalidateCurrentCodes($user->getGoogleAuthenticatorSecret());

            return new GeneratedCodes(
                array_map(
                    fn () => $this->createCode($user->getGoogleAuthenticatorSecret())->getCode(),
                    array_fill(0, $recoveryCode->getCount(), null)
                )
            );
        }

        throw new \RuntimeException('Unable to create recovery codes for non-2fa users');
    }

    /**
     * Remove all recovery codes for the current secret from the database.
     */
    private function invalidateCurrentCodes(string $secret): void
    {
        $currentCodes = $this->doctrine->getRepository(RecoveryCodeEntity::class)->findBy(['secret' => $secret]);
        $manager      = $this->doctrine->getManagerForClass(RecoveryCodeEntity::class);

        array_walk($currentCodes, fn (RecoveryCodeEntity $recoveryCode) => $manager->remove($recoveryCode));
        $manager->flush();
    }

    /**
     * Create one new recovery code in the database.
     */
    private function createCode(string $secret): RecoveryCodeEntity
    {
        $manager      = $this->doctrine->getManagerForClass(RecoveryCodeEntity::class);
        $recoveryCode = new RecoveryCodeEntity();
        $recoveryCode->setSecret($secret);
        $recoveryCode->setCode($this->generateRecoveryCode());

        $manager->persist($recoveryCode);
        $manager->flush();

        return $recoveryCode;
    }

    /**
     * Generate a random recover code string formatted like: XXXXX XXXXX XXXXX.
     */
    private function generateRecoveryCode(): string
    {
        return sprintf(
            '%s %s %s',
            str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT),
            str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT),
            str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT)
        );
    }
}
