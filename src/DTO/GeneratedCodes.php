<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace Harborn\SecureJWTBundle\DTO;

final class GeneratedCodes
{
    private array $codes;

    public function __construct(array $codes)
    {
        $this->codes = $codes;
    }

    public function getCodes(): array
    {
        return $this->codes;
    }
}
