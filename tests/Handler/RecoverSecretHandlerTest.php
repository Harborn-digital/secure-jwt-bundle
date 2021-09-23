<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Tests\Handler;

use ConnectHolland\SecureJWTBundle\Entity\RecoveryCode;
use ConnectHolland\SecureJWTBundle\Exception\TwoFactorSecretNotSetupException;
use ConnectHolland\SecureJWTBundle\Handler\RecoverSecretHandler;
use ConnectHolland\SecureJWTBundle\Message\RecoverSecret;
use ConnectHolland\SecureJWTBundle\Tests\Fixture\User;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class RecoverSecretHandlerTest extends TestCase
{
    public function testNoExceptionForIncorrectCode(): void
    {
        $doctrine   = $this->createMock(ManagerRegistry::class);
        $repository = $this->createMock(EntityRepository::class);
        $user       = new User();

        $user->setGoogleAuthenticatorSecret('verysecret!');

        $doctrine
            ->method('getRepository')
            ->willReturn($repository);

        $repository
            ->expects($this->once())
            ->method('findBy');

        $handler = new RecoverSecretHandler($doctrine);
        $handler(new RecoverSecret($user, '12345 54321 09876'));
    }

    public function testExceptionForCorrectCode(): void
    {
        $this->expectException(TwoFactorSecretNotSetupException::class);

        $doctrine   = $this->createMock(ManagerRegistry::class);
        $repository = $this->createMock(EntityRepository::class);
        $manager    = $this->createMock(EntityManager::class);
        $user       = new User();

        $user->setGoogleAuthenticatorSecret('verysecret!');
        $user->setGoogleAuthenticatorConfirmed(true);

        $doctrine
            ->method('getRepository')
            ->willReturn($repository);

        $doctrine
            ->method('getManager')
            ->willReturn($manager);

        $repository
            ->expects($this->once())
            ->method('findBy')
            ->willReturn(new RecoveryCode());

        $manager
            ->expects($this->once())
            ->method('flush');

        $handler = new RecoverSecretHandler($doctrine);
        $handler(new RecoverSecret($user, '12345 54321 09876'));

        $this->assertSame('', $user->getGoogleAuthenticatorSecret());
        $this->assertFalse($user->isGoogleAuthenticatorConfirmed());
    }
}
