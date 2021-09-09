<?php

namespace ConnectHolland\SecureJWTBundle\Resolver;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Integer;

class RememberDeviceResolver
{
    private $configuration;

    private $debug;

    public function __construct(array $configuration, bool $debug = false)
    {
        $this->configuration = $configuration;
        $this->debug = $debug;
    }

    public function getRememberDeviceStatus(): bool
    {
        return $this->configuration["is_remembered"];
    }


    public function getRememberDeviceExpiryDays(): int
    {
        return $this->configuration["expiry_days"];
    }



}