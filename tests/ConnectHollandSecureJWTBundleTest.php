<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Tests;

use ConnectHolland\SecureJWTBundle\ConnectHollandSecureJWTBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConnectHollandSecureJWTBundleTest extends TestCase
{
    public function testSecurityFactoryIsAdded(): void
    {
        $bundle    = new ConnectHollandSecureJWTBundle();
        $container = $this->createMock(ContainerBuilder::class);
        $extension = $this->createMock(SecurityExtension::class);

        $container
            ->expects($this->once())
            ->method('getExtension')
            ->willReturn($extension);

        $extension
            ->expects($this->once())
            ->method('addSecurityListenerFactory');

        $bundle->build($container);
    }
}
