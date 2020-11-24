<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="secure_jwt_recovery_code")
 *
 * @codeCoverageIgnore Trivial entity with only getters and setters.
 *
 * Recovery codes are tied to authenticator secrets, so they will
 * always be invalidated by design if a new secret is set.
 */
class RecoveryCode
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private string $code;

    /**
     * @ORM\Column(type="string")
     */
    private string $secret;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $downloaded = false;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    public function isDownloaded(): bool
    {
        return $this->downloaded;
    }

    public function setDownloaded(bool $downloaded): void
    {
        $this->downloaded = $downloaded;
    }
}
