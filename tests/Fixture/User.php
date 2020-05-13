<?php

/*
 * This file is part of the Connect Holland Secure JWT package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\SecureJWT\Tests\Fixture;

use ConnectHolland\SecureJWT\Entity\TwoFactorUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements TwoFactorUserInterface, UserInterface
{
    public ?string $googleAuthenticatorSecret = null;

    public array $roles = [];

    public string $password = 'secret';

    public string $salt = '';

    public string $username = 'user';

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
}
