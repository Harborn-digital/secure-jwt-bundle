<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace Harborn\SecureJWTBundle\Tests\DependencyInjection;

use Harborn\SecureJWTBundle\DependencyInjection\HarbornSecureJWTExtension;
use Harborn\SecureJWTBundle\EventSubscriber\LoginSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class HarbornSecureJWTExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $extension = new HarbornSecureJWTExtension();
        $extension->load([], $container);

        $this->assertTrue($container->has('harborn-digital.secure_jwt.two_factor_jwt.listener'), 'Security listener should be loaded');
        $this->assertTrue($container->has(LoginSubscriber::class), 'Auto configuration should load LoginSubscriber');
    }
}
