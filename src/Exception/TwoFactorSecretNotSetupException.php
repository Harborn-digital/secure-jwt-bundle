<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Exception;

use ConnectHolland\SecureJWTBundle\Entity\TwoFactorUserInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Throwable;

class TwoFactorSecretNotSetupException extends AuthenticationException
{
    private TwoFactorUserInterface $user;

    public function __construct(TwoFactorUserInterface $user, $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->user = $user;
    }

    public function getUser(): TwoFactorUserInterface
    {
        return $this->user;
    }
}
