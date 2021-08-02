<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Tests\Security\Http\Authentication;

use ConnectHolland\SecureJWTBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler as LexikAuthenticationSuccessHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Variant of Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler.
 */
class AuthenticationSuccessHandlerTest extends TestCase
{
    /**
     * test onAuthenticationSuccess method.
     */
    public function testOnAuthenticationSuccess(): void
    {
        $request = $this->getRequest();
        $token   = $this->getToken();

        $response = (new AuthenticationSuccessHandler(new LexikAuthenticationSuccessHandler($this->getJWTManager('secrettoken'), $this->getDispatcher()), $this->getEncoder(), 'strict'))
            ->onAuthenticationSuccess($request, $token);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $cookies = $response->headers->getCookies();
        $this->assertArrayNotHasKey('token', $content);
        $this->assertArrayHasKey('result', $content);
        $this->assertArrayHasKey('payload', $content);
        $this->assertArrayHasKey('user', $content['payload']);
        $this->assertSame('example@example.org', $content['payload']['user']);
        $this->assertCount(1, $cookies);
        $this->assertSame('secrettoken', $cookies[0]->getValue());
        $this->assertSame(1627902433, $cookies[0]->getExpiresTime());
    }

    public function testHandleAuthenticationSuccess()
    {
        $response = (new AuthenticationSuccessHandler(new LexikAuthenticationSuccessHandler($this->getJWTManager('secrettoken'), $this->getDispatcher()), $this->getEncoder(), 'strict'))
            ->handleAuthenticationSuccess($this->getUser());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $cookies = $response->headers->getCookies();

        $this->assertArrayNotHasKey('token', $content);
        $this->assertArrayHasKey('result', $content);
        $this->assertCount(1, $cookies);
        $this->assertSame('secrettoken', $cookies[0]->getValue());
    }

    /**
     * @dataProvider provideSameSiteOptions
     */
    public function testHandleAuthenticationSuccessWithGivenJWT(string $sameSite)
    {
        $response = (new AuthenticationSuccessHandler(new LexikAuthenticationSuccessHandler($this->getJWTManager('secrettoken'), $this->getDispatcher()), $this->getEncoder(), $sameSite))
            ->handleAuthenticationSuccess($this->getUser(), 'jwt');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $cookies = $response->headers->getCookies();

        $this->assertArrayNotHasKey('token', $content);
        $this->assertArrayHasKey('result', $content);
        $this->assertCount(1, $cookies);
        $this->assertSame('jwt', $cookies[0]->getValue());
        $this->assertSame($sameSite, $cookies[0]->getSameSite());
        $this->assertTrue($cookies[0]->isHttpOnly());
        $this->assertTrue($cookies[0]->isSecure());
    }

    private function getEncoder(): JWTEncoderInterface
    {
        $encoder = $this->createMock(JWTEncoderInterface::class);

        $encoder
            ->expects($this->once())
            ->method('decode')
            ->willReturn(['user' => 'example@example.org', 'exp' => 1627902433]);

        return $encoder;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRequest()
    {
        $request = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        return $request;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getToken()
    {
        $token = $this
            ->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken')
            ->disableOriginalConstructor()
            ->getMock();

        $token
            ->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($this->getUser()));

        return $token;
    }

    private function getUser()
    {
        $user = $this
            ->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')
            ->getMock();

        $user
            ->expects($this->any())
            ->method('getUsername')
            ->will($this->returnValue('username'));

        return $user;
    }

    private function getJWTManager($token = null)
    {
        $jwtManager = $this->getMockBuilder(JWTManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        if (null !== $token) {
            $jwtManager
                ->expects($this->any())
                ->method('create')
                ->will($this->returnValue('secrettoken'));
        }

        return $jwtManager;
    }

    private function getDispatcher()
    {
        $dispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(AuthenticationSuccessEvent::class),
                $this->equalTo(Events::AUTHENTICATION_SUCCESS)
            );

        return $dispatcher;
    }

    public function provideSameSiteOptions(): array
    {
        return [
            ['strict'],
            ['lax'],
            ['none'],
        ];
    }
}
