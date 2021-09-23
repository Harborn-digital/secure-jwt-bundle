<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Tests\DependencyInjection;

use ConnectHolland\SecureJWTBundle\DependencyInjection\ConnectHollandSecureJWTExtension;
use ConnectHolland\SecureJWTBundle\EventSubscriber\LoginSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConnectHollandSecureJWTExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $extension = new ConnectHollandSecureJWTExtension();
        $extension->load([], $container);

        $this->assertTrue($container->has('connectholland.secure_jwt.two_factor_jwt.listener'), 'Security listener should be loaded');
        $this->assertTrue($container->has(LoginSubscriber::class), 'Auto configuration should load LoginSubscriber');
    }
}
