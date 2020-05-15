<?php

/*
 * This file is part of the Connect Holland Secure JWT package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Message;

use ApiPlatform\Core\Annotation\ApiResource;

/**
 * Create recovery codes for the current user. Will invalidate any existing codes for that user.
 *
 * @ApiResource(
 *     messenger=true,
 *     collectionOperations={
 *       "post"={"status"=200}
 *     },
 *     itemOperations={}
 * )
 *
 * @codeCoverageIgnore Trivial class with only a getter
 */
final class RecoveryCode
{
    private int $count;

    public function __construct(int $count)
    {
        $this->count = $count;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
