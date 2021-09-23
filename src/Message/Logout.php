<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Message;

use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource(
 *     messenger=true,
 *     collectionOperations={
 *       "post"={"status"=200}
 *     },
 *     itemOperations={},
 *     output=false
 * )
 *
 * @codeCoverageIgnore Trivial class with only a getter
 */
final class Logout
{
    private string $logout;

    public function getLogout(): string
    {
        throw new \RuntimeException('The logout attribute only exists because API platform requires at least one attribute in the message. Do not use this for anything other than that.');
    }
}
