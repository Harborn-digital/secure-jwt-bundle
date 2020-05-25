<?php

/*
 * This file is part of the Connect Holland Secure JWT package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Security\Http\Authentication\Provider;

use ConnectHolland\SecureJWTBundle\Entity\TwoFactorUserInterface;
use ConnectHolland\SecureJWTBundle\Exception\TwoFactorAuthenticationMissingException;
use ConnectHolland\SecureJWTBundle\Exception\TwoFactorSecretNotSetupException;
use ConnectHolland\SecureJWTBundle\Message\RecoverSecret;
use ConnectHolland\SecureJWTBundle\Security\Token\TwoFactorJWTToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class TwoFactorJWTProvider extends DaoAuthenticationProvider
{
    private GoogleAuthenticatorInterface $googleAuthenticator;

    private MessageBusInterface $messageBus;

    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, EncoderFactoryInterface $encoderFactory, GoogleAuthenticatorInterface $googleAuthenticator, MessageBusInterface $messageBus, bool $hideUserNotFoundExceptions = true)
    {
        parent::__construct($userProvider, $userChecker, 'two_factor_jwt', $encoderFactory, $hideUserNotFoundExceptions);

        $this->googleAuthenticator = $googleAuthenticator;
        $this->messageBus          = $messageBus;
    }

    public function supports(TokenInterface $token): bool
    {
        return $token instanceof TwoFactorJWTToken;
    }

    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token): void
    {
        // Check normal username / password
        parent::checkAuthentication($user, $token);

        if (!$user instanceof TwoFactorUserInterface) {
            throw new BadCredentialsException(sprintf('Invalid user, received "%s" but require "%s"', get_class($user), TwoFactorUserInterface::class));
        }

        if (null === $user->getGoogleAuthenticatorSecret()) {
            throw new TwoFactorSecretNotSetupException($user, 'Please set up two factor auth app');
        }

        if ('' === $token->getTwoFactorChallenge()) {
            throw $user->isGoogleAuthenticatorConfirmed() ? new TwoFactorAuthenticationMissingException('Please provide two factor code to continue login') : new TwoFactorSecretNotSetupException($user, 'Please set up two factor auth app');
        }

        // Will throw an exception to setup 2fa if the recovery code is valid
        $this->messageBus->dispatch(new RecoverSecret($user, $token->getTwoFactorChallenge()));

        if (!$this->googleAuthenticator->checkCode($user, $token->getTwoFactorChallenge())) {
            throw new BadCredentialsException('Incorrect challenge');
        }
    }
}
