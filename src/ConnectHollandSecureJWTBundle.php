<?php

/*
 * This file is part of the Connect Holland Secure JWT package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle;

use ConnectHolland\SecureJWTBundle\Security\Factory\TwoFactoryJWTFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ConnectHollandSecureJWTBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new TwoFactoryJWTFactory());
    }
}
