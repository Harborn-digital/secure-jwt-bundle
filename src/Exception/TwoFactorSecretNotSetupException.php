<?php

/*
 * This file is part of the Connect Holland Secure JWT package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\SecureJWT\Exception;

use ConnectHolland\SecureJWT\Entity\TwoFactorUserInterface;
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
