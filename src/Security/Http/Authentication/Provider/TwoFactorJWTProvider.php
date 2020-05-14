<?php

/*
 * This file is part of the Connect Holland Secure JWT package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Security\Http\Authentication\Provider;

use ConnectHolland\SecureJWTBundle\Entity\TwoFactorUserInterface;
use ConnectHolland\SecureJWTBundle\Exception\TwoFactorAuthenticationMissingException;
use ConnectHolland\SecureJWTBundle\Exception\TwoFactorSecretNotSetupException;
use ConnectHolland\SecureJWTBundle\Security\Token\TwoFactorJWTToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
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

    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, EncoderFactoryInterface $encoderFactory, GoogleAuthenticatorInterface $googleAuthenticator, bool $hideUserNotFoundExceptions = true)
    {
        parent::__construct($userProvider, $userChecker, 'two_factor_jwt', $encoderFactory, $hideUserNotFoundExceptions);

        $this->googleAuthenticator = $googleAuthenticator;
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

        if (!$this->googleAuthenticator->checkCode($user, $token->getTwoFactorChallenge())) {
            throw new BadCredentialsException('Incorrect challenge');
        }
    }
}
