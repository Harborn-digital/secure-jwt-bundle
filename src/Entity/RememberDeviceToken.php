<?php

namespace ConnectHolland\SecureJWTBundle\Entity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="remember_device_invalid_token")
 *
 * @codeCoverageIgnore Trivial entity with only getters and setters.
 */

class RememberDeviceToken
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
     * @ORM\Column(type="text")
     */
    private string $username;

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

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

}