<?php

/*
 * This file is part of the Connect Holland Secure JWT package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Tests\Swagger;

use ConnectHolland\SecureJWTBundle\Swagger\LoginDecorator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LoginDecoratorTest extends TestCase
{
    public function testDocumentsAuthentication(): void
    {
        $decorated  = $this->createMock(NormalizerInterface::class);
        $normalizer = new LoginDecorator($decorated);

        $decorated
            ->expects($this->once())
            ->method('normalize')
            ->willReturn([]);

        $docs = $normalizer->normalize(new \stdClass());

        $this->assertContains('Authentication', $docs['paths']['/api/logouts']['post']['tags']);
        $this->assertContains('Authentication', $docs['paths']['/api/login_check']['post']['tags']);
    }
}
