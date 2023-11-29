<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Tests\Handler;

use ConnectHolland\SecureJWTBundle\Entity\RecoveryCode as RecoveryCodeEntity;
use ConnectHolland\SecureJWTBundle\Handler\DownloadedRecoveryCodeHandler;
use ConnectHolland\SecureJWTBundle\Message\DownloadedRecoveryCode;
use ConnectHolland\SecureJWTBundle\Tests\Fixture\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class DownloadedRecoveryCodeHandlerTest extends TestCase
{
    public function testSetCodesDownloadedSuccessful(): void
    {
        $doctrine      = $this->createMock(ManagerRegistry::class);
        $manager       = $this->createMock(EntityManager::class);
        $repository    = $this->createMock(EntityRepository::class);
        $tokenStorage  = new TokenStorage();
        $user          = new User();
        $recoveryCodes = [
            new RecoveryCodeEntity(),
            new RecoveryCodeEntity(),
            new RecoveryCodeEntity()
        ];

        $user->setGoogleAuthenticatorSecret('verysecret!');
        $tokenStorage->setToken(new JWTUserToken([], $user));

        $repository
            ->method('findBy')
            ->willReturn($recoveryCodes);

        $manager
            ->expects($this->exactly(1))
            ->method('flush');

        $doctrine
            ->method('getManagerForClass')
            ->willReturn($manager);

        $doctrine
            ->method('getRepository')
            ->willReturn($repository);

        $handler = new DownloadedRecoveryCodeHandler($doctrine, $tokenStorage);
        $handler(new DownloadedRecoveryCode(true));
        foreach ($recoveryCodes as $code) {
            $this->assertSame(true, $code->isDownloaded());
        }
    }
}
