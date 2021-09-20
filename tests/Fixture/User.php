<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Tests\Fixture;

use ConnectHolland\SecureJWTBundle\Entity\TwoFactorUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements TwoFactorUserInterface, UserInterface
{
    public ?string $googleAuthenticatorSecret = null;

    public array $roles = [];

    public string $password = 'secret';

    public string $salt = '';

    public string $username = 'user';

    public bool $confirmed = false;

    public function getGoogleAuthenticatorSecret(): ?string
    {
        return $this->googleAuthenticatorSecret;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getSalt(): string
    {
        return $this->salt;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function eraseCredentials(): void
    {
    }

    public function setGoogleAuthenticatorSecret(string $secret): void
    {
        $this->googleAuthenticatorSecret = $secret;
    }

    public function isGoogleAuthenticatorEnabled(): bool
    {
        return '' === $this->googleAuthenticatorSecret;
    }

    public function getGoogleAuthenticatorUsername(): string
    {
        return $this->username;
    }

    public function isGoogleAuthenticatorConfirmed(): bool
    {
        return $this->confirmed;
    }

    public function setGoogleAuthenticatorConfirmed(bool $confirmed): void
    {
        $this->confirmed = $confirmed;
    }
}
