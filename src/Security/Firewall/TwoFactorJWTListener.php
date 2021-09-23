<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Security\Firewall;

use ConnectHolland\SecureJWTBundle\Security\Token\TwoFactorJWTToken;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\ParameterBagUtils;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TwoFactorJWTListener extends AbstractAuthenticationListener
{
    /**
     * Default options.
     */
    private const DEFAULTS = [
        'username_parameter'  => 'username',
        'password_parameter'  => 'password',
        'challenge_parameter' => 'challenge',
        'post_only'           => true,
    ];

    /**
     * TwoFactorAuthenticationListener constructor.
     *
     * @codeCoverageIgnore Ignore because it's a trivial constructor.
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        SessionAuthenticationStrategyInterface $sessionStrategy,
        HttpUtils $httpUtils,
        string $providerKey,
        AuthenticationSuccessHandlerInterface $successHandler,
        AuthenticationFailureHandlerInterface $failureHandler,
        array $options = [],
        LoggerInterface $logger = null,
        EventDispatcherInterface $dispatcher = null
    ) {
        parent::__construct(
            $tokenStorage,
            $authenticationManager,
            $sessionStrategy,
            $httpUtils,
            $providerKey,
            $successHandler,
            $failureHandler,
            array_merge(self::DEFAULTS, $options),
            $logger,
            $dispatcher
        );
    }

    protected function requiresAuthentication(Request $request): bool
    {
        if (!$request->isMethod('POST')) {
            return false;
        }

        // Ignore coverage because this is just calling the parent
        return parent::requiresAuthentication($request); // @codeCoverageIgnore
    }

    protected function attemptAuthentication(Request $request): TokenInterface
    {
        $username  = trim(ParameterBagUtils::getParameterBagValue($request->request, $this->options['username_parameter']));
        $password  = ParameterBagUtils::getParameterBagValue($request->request, $this->options['password_parameter']) ?: '';
        $challenge = trim(ParameterBagUtils::getParameterBagValue($request->request, $this->options['challenge_parameter']) ?: '');

        return $this->authenticationManager->authenticate(new TwoFactorJWTToken($username, $password, $challenge, $this->providerKey));
    }
}
