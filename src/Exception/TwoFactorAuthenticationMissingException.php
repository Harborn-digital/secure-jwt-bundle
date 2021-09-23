<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TwoFactorAuthenticationMissingException extends AuthenticationException
{
}
