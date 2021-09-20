<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Message;

use ConnectHolland\SecureJWTBundle\Entity\TwoFactorUserInterface;

final class RecoverSecret
{
    private TwoFactorUserInterface $user;

    private string $code;

    /**
     * RecoverSecret constructor.
     */
    public function __construct(TwoFactorUserInterface $user, string $code)
    {
        $this->user = $user;
        $this->code = $code;
    }

    public function getUser(): TwoFactorUserInterface
    {
        return $this->user;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
