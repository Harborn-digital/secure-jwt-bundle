<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Resolver;

class RememberDeviceResolver
{
    private $configuration;

    private $debug;

    public function __construct(array $configuration, bool $debug = false)
    {
        $this->configuration = $configuration;
        $this->debug         = $debug;
    }

    public function getRememberDeviceStatus(): bool
    {
        return $this->configuration['is_remembered'];
    }

    public function getRememberDeviceExpiryDays(): int
    {
        return $this->configuration['expiry_days'];
    }

    /**
     * Change the configuration by passing a key-value array.
     */
    public function setConfiguration(array $configuration): void
    {
        $this->configuration = $configuration;
    }
}
