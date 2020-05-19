<?php

/*
 * This file is part of the Connect Holland Secure JWT package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\SecureJWT\Entity;

use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;

interface TwoFactorUserInterface extends TwoFactorInterface
{
    public function setGoogleAuthenticatorSecret(string $secret): void;

    public function isGoogleAuthenticatorConfirmed(): bool;

    public function setGoogleAuthenticatorConfirmed(bool $confirmed): void;
}
