<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace Harborn\SecureJWTBundle\Tests\Security\Firewall;

use Harborn\SecureJWTBundle\Security\Token\TwoFactorJWTToken;
use Harborn\SecureJWTBundle\Tests\Fixture\TwoFactorJWTListenerFixture;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class TwoFactorJWTListenerTest extends TestCase
{
    public function testRequiresPost(): void
    {
        $twoFactorJWTListener = new TwoFactorJWTListenerFixture(true);
        $request              = new Request();

        $this->assertFalse($twoFactorJWTListener->publicRequiresAuthentication($request));
    }

    public function testAttemptAuthentication(): void
    {
        $twoFactorJWTListener = new TwoFactorJWTListenerFixture(true);
        $request              = new Request([],
            [
                'username'  => 'user',
                'password'  => 'pass',
                'challenge' => '123',
            ]
        );

        $token = $twoFactorJWTListener->publicAttemptAuthentication($request);

        $this->assertInstanceOf(TwoFactorJWTToken::class, $token);
    }

    public function testFailedAttemptAuthentication(): void
    {
        $this->expectException(BadCredentialsException::class);

        $twoFactorJWTListener = new TwoFactorJWTListenerFixture(false);
        $request              = new Request([],
            [
                'username'  => 'user',
                'password'  => 'pass',
                'challenge' => '123',
            ]
        );

        $token = $twoFactorJWTListener->publicAttemptAuthentication($request);
    }
}
