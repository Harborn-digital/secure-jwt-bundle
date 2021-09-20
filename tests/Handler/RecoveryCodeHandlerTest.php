<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Tests\Handler;

use ConnectHolland\SecureJWTBundle\Entity\RecoveryCode as RecoveryCodeEntity;
use ConnectHolland\SecureJWTBundle\Handler\RecoveryCodeHandler;
use ConnectHolland\SecureJWTBundle\Message\RecoveryCode;
use ConnectHolland\SecureJWTBundle\Tests\Fixture\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class RecoveryCodeHandlerTest extends TestCase
{
    public function testCreateRecoveryCodes(): void
    {
        $doctrine     = $this->createMock(ManagerRegistry::class);
        $manager      = $this->createMock(EntityManager::class);
        $repository   = $this->createMock(EntityRepository::class);
        $tokenStorage = new TokenStorage();
        $user         = new User();

        $user->setGoogleAuthenticatorSecret('verysecret!');
        $tokenStorage->setToken(new JWTUserToken([], $user));

        $repository
            ->method('findBy')
            ->willReturn(
                [
                    new RecoveryCodeEntity(),
                    new RecoveryCodeEntity(),
                    new RecoveryCodeEntity(),
                ]
            );

        $manager
            ->expects($this->exactly(3))
            ->method('remove');

        $manager
            ->expects($this->exactly(5))
            ->method('persist');

        $manager
            ->expects($this->exactly(6))
            ->method('flush');

        $doctrine
            ->method('getManagerForClass')
            ->willReturn($manager);

        $doctrine
            ->method('getRepository')
            ->willReturn($repository);

        $handler = new RecoveryCodeHandler($doctrine, $tokenStorage);
        $codes   = $handler(new RecoveryCode(5));

        $this->assertCount(5, $codes->getCodes());
        foreach ($codes as $code) {
            $this->assertInstanceOf(RecoveryCodeEntity::class, $code);
            $this->assertSame('verysecret!', $code->getSecret());
            $this->assertMatchesRegularExpression('/^\d{5}\ \d{5}\ \d{5}$/', $code->getCode());
        }
    }
}
