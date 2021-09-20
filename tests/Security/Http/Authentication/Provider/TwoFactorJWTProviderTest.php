<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Tests\Security\Http\Authentication\Provider;

use ConnectHolland\SecureJWTBundle\Exception\TwoFactorAuthenticationMissingException;
use ConnectHolland\SecureJWTBundle\Exception\TwoFactorSecretNotSetupException;
use ConnectHolland\SecureJWTBundle\Security\Guard\JWTTokenAuthenticator;
use ConnectHolland\SecureJWTBundle\Security\Http\Authentication\Provider\TwoFactorJWTProvider;
use ConnectHolland\SecureJWTBundle\Security\Token\TwoFactorJWTToken;
use ConnectHolland\SecureJWTBundle\Tests\Fixture\User;
use Doctrine\Common\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use PHPUnit\Framework\TestCase;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class TwoFactorJWTProviderTest extends TestCase
{
    private TwoFactorJWTProvider $provider;

    private UserProviderInterface $userProvider;

    private EncoderFactoryInterface $encoderFactory;

    private GoogleAuthenticatorInterface $authenticator;

    private MessageBusInterface $messageBus;

    private JWTEncoderInterface $jwtEncoder;

    private RequestStack $requestStack;

    private JWTTokenAuthenticator $JWTTokenAuthenticator;

    private ManagerRegistry $doctrine;

    public function setUp(): void
    {
        $this->userProvider          = $this->createMock(UserProviderInterface::class);
        $this->encoderFactory        = $this->createMock(EncoderFactoryInterface::class);
        $this->authenticator         = $this->createMock(GoogleAuthenticatorInterface::class);
        $this->jwtEncoder            = $this->createMock(JWTEncoderInterface::class);
        $this->requestStack          = $this->createMock(RequestStack::class);
        $this->JWTTokenAuthenticator = $this->createMock(JWTTokenAuthenticator::class);
        $this->doctrine              = $this->createMock(ManagerRegistry::class);
        $this->messageBus            = new MessageBus();

        $this->provider = new TwoFactorJWTProvider(
            $this->userProvider, $this->createMock(UserCheckerInterface::class), $this->encoderFactory, $this->authenticator, $this->messageBus, $this->jwtEncoder, $this->requestStack, $this->JWTTokenAuthenticator, $this->doctrine, false,
        );
    }

    /**
     * @dataProvider provideTestTokens
     */
    public function testSupports(TokenInterface $token, bool $supported): void
    {
        $this->assertSame($supported, $this->provider->supports($token));
    }

    public function provideTestTokens(): array
    {
        return [
            'UsernamePasswordToken not supported' => [new UsernamePasswordToken(new User(), '', 'test'), false],
            'TwoFactorJWTToken is supported'      => [new TwoFactorJWTToken('user', '', '123456', 'test'), true],
        ];
    }

    /**
     * @dataProvider provideTestAuthentication
     */
    public function testCheckAuthentication(TwoFactorJWTToken $token, UserInterface $user, \Exception $exception = null): void
    {
        if ($exception instanceof \Exception) {
            $this->expectException(get_class($exception));
        }

        $this->userProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->willReturn($user);

        $this->encoderFactory
            ->method('getEncoder')
            ->willReturn(new PlaintextPasswordEncoder());

        $this->authenticator
            ->method('checkCode')
            ->willReturnCallback(fn (User $user, string $code): bool => '654321' === $code);

        $this->provider->authenticate($token);
    }

    public function provideTestAuthentication(): array
    {
        $user1 = new User();
        $user1->setPassword('password');

        $user2 = new User();
        $user2->setPassword('password');
        $user2->setGoogleAuthenticatorSecret('secret');

        $user3 = new User();
        $user3->setPassword('password');
        $user3->setGoogleAuthenticatorSecret('secret');
        $user3->setGoogleAuthenticatorConfirmed(true);

        return [
            'Incorrect User gives bad credentials' => [
                new TwoFactorJWTToken('test', 'password', '123456', 'test'),
                new class() implements UserInterface {
                    public function getRoles()
                    {
                        // TODO: Implement getRoles() method.
                    }

                    public function getPassword()
                    {
                        return 'password';
                    }

                    public function getSalt()
                    {
                        return '';
                    }

                    public function getUsername()
                    {
                        // TODO: Implement getUsername() method.
                    }

                    public function eraseCredentials()
                    {
                        // TODO: Implement eraseCredentials() method.
                    }
                },
                new BadCredentialsException(),
            ],
            'No password gives bad credentials' => [
                new TwoFactorJWTToken('test', '', '123456', 'test'),
                new User(),
                new BadCredentialsException(),
            ],
            'Setup 2FA no secret' => [
                new TwoFactorJWTToken('test', 'password', '', 'test'),
                $user1,
                new TwoFactorSecretNotSetupException($user1),
            ],
            'Setup 2FA not confirmed' => [
                new TwoFactorJWTToken('test', 'password', '', 'test'),
                $user2,
                new TwoFactorSecretNotSetupException($user2),
            ],
            'Verify if presented but not confirmed' => [
                new TwoFactorJWTToken('test', 'password', '654321', 'test'),
                $user2,
            ],
            'Present challenge' => [
                new TwoFactorJWTToken('test', 'password', '', 'test'),
                $user3,
                new TwoFactorAuthenticationMissingException(),
            ],
            'Incorrect code' => [
                new TwoFactorJWTToken('test', 'password', '123456', 'test'),
                $user3,
                new BadCredentialsException(),
            ],
            'Correct code' => [
                new TwoFactorJWTToken('test', 'password', '654321', 'test'),
                $user3,
            ],
        ];
    }
}
