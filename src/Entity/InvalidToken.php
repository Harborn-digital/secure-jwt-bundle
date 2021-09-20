<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="secure_jwt_invalid_token")
 *
 * @codeCoverageIgnore Trivial entity with only getters and setters.
 */
class InvalidToken
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="text")
     */
    private string $token;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $invalidatedAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getInvalidatedAt(): \DateTime
    {
        return $this->invalidatedAt;
    }

    public function setInvalidatedAt(\DateTime $invalidatedAt): void
    {
        $this->invalidatedAt = $invalidatedAt;
    }
}
