<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace Harborn\SecureJWTBundle;

use Harborn\SecureJWTBundle\Security\Factory\TwoFactoryJWTFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HarbornSecureJWTBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new TwoFactoryJWTFactory());
    }
}
