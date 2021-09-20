<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @codeCoverageIgnore No tests because all code is trivial
 */
class TwoFactoryJWTFactory extends AbstractFactory
{
    public function getPosition(): string
    {
        return 'form';
    }

    public function getKey(): string
    {
        return 'two_factor_jwt';
    }

    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId): string
    {
        return 'connectholland.secure_jwt.two_factor_jwt.provider';
    }

    protected function getListenerId(): string
    {
        return 'connectholland.secure_jwt.two_factor_jwt.listener';
    }
}
