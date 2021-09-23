<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Handler;

use ConnectHolland\SecureJWTBundle\Entity\RecoveryCode;
use ConnectHolland\SecureJWTBundle\Exception\TwoFactorSecretNotSetupException;
use ConnectHolland\SecureJWTBundle\Message\RecoverSecret;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RecoverSecretHandler implements MessageHandlerInterface
{
    private ManagerRegistry $doctrine;

    /**
     * RecoverSecretHandler constructor.
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function __invoke(RecoverSecret $recoverSecret): void
    {
        $recoveryCode = $this->doctrine->getRepository(RecoveryCode::class)->findBy(['secret' => $recoverSecret->getUser()->getGoogleAuthenticatorSecret(), 'code' => $recoverSecret->getCode()]);

        if ($recoveryCode instanceof RecoveryCode) {
            $recoverSecret->getUser()->setGoogleAuthenticatorSecret('');
            $recoverSecret->getUser()->setGoogleAuthenticatorConfirmed(false);
            $this->doctrine->getManager()->flush();

            throw new TwoFactorSecretNotSetupException($recoverSecret->getUser(), 'Please set up two factor auth app');
        }
    }
}
