<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Message;

use ApiPlatform\Core\Annotation\ApiResource;

/**
 * Create recovery codes for the current user. Will invalidate any existing codes for that user.
 *
 * @ApiResource(
 *     messenger=true,
 *     collectionOperations={},
 *     itemOperations={
 *       "post"={"status"=200}
 *     },
 * )
 *
 * @codeCoverageIgnore Trivial class with only a getter
 */
class DownloadedRecoveryCode
{
    private bool $value;

    public function __construct(bool $value)
    {
        $this->value = $value;
    }

    public function getValue(): bool
    {
        return $this->value;
    }
}
