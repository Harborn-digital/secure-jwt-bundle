<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Entity;

use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;

interface TwoFactorUserInterface extends TwoFactorInterface
{
    public function setGoogleAuthenticatorSecret(string $secret): void;

    public function isGoogleAuthenticatorConfirmed(): bool;

    public function setGoogleAuthenticatorConfirmed(bool $confirmed): void;
}
