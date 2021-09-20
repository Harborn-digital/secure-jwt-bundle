<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Tests\Fixture;

use ConnectHolland\SecureJWTBundle\Security\Firewall\TwoFactorJWTListener;
use ConnectHolland\SecureJWTBundle\Security\Token\TwoFactorJWTToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class TwoFactorJWTListenerFixture extends TwoFactorJWTListener
{
    public function __construct(bool $success)
    {
        $this->authenticationManager = new class($success) {
            private bool $success;

            public function __construct(bool $success)
            {
                $this->success = $success;
            }

            public function authenticate(TwoFactorJWTToken $token): TokenInterface
            {
                if ($this->success) {
                    return $token;
                }

                throw new BadCredentialsException('Not authenticated');
            }
        };

        $this->options = [
            'username_parameter'  => 'username',
            'password_parameter'  => 'password',
            'challenge_parameter' => 'challenge',
        ];

        $this->providerKey = 'unit-test';
    }

    public function publicRequiresAuthentication(Request $request): bool
    {
        return $this->requiresAuthentication($request);
    }

    public function publicAttemptAuthentication(Request $request): TokenInterface
    {
        return $this->attemptAuthentication($request);
    }
}
