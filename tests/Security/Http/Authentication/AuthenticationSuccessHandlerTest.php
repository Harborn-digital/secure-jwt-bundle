<?php

/*
 * This file is part of the Connect Holland Secure JWT package and distributed under the terms of the MIT License.
 * Copyright (c) 2020-2021 Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Tests\Security\Http\Authentication;

use ConnectHolland\SecureJWTBundle\Resolver\RememberDeviceResolver;
use ConnectHolland\SecureJWTBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Doctrine\Persistence\ManagerRegistry;
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

        $response = (new AuthenticationSuccessHandler(new LexikAuthenticationSuccessHandler($this->getJWTManager('secrettoken'), $this->getDispatcher()), $this->getEncoder(), 'strict', $this->getRememberDeviceResolver(false), $this->getDoctrine()))
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
        $response = (new AuthenticationSuccessHandler(new LexikAuthenticationSuccessHandler($this->getJWTManager('secrettoken'), $this->getDispatcher()), $this->getEncoder(), 'strict', $this->getRememberDeviceResolver(false), $this->getDoctrine()))
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
        $response = (new AuthenticationSuccessHandler(new LexikAuthenticationSuccessHandler($this->getJWTManager('secrettoken'), $this->getDispatcher()), $this->getEncoder(), $sameSite, $this->getRememberDeviceResolver(false), $this->getDoctrine()))
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

    public function testRememberDeviceCookieIsSetAfterAuthenticationSuccess()
    {
        $request = $this->getRequest();
        $token   = $this->getToken();

        $response = (new AuthenticationSuccessHandler(new LexikAuthenticationSuccessHandler($this->getJWTManager('secrettoken'), $this->getDispatcher()), $this->getEncoder(), 'strict', $this->getRememberDeviceResolver(true), $this->getDoctrine()))
            ->onAuthenticationSuccess($request, $token);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $cookies = $response->headers->getCookies();
        $this->assertCount(2, $cookies);
        $this->assertSame('BEARER', $cookies[0]->getName());
        $this->assertSame('REMEMBER_DEVICE', $cookies[1]->getName());
        $this->assertSame('encoded_value', $cookies[1]->getValue());

    }

    public function testRememberDeviceCookieIsReplacedAfterNewAuthenticationSuccess()
    {
        $request = $this->getRequest();
        $token   = $this->getToken();
        $manager = $this->getJWTManager('secrettoken');
        $dispatcher = $this->getDispatcher();
        $encoder = $this->getEncoder();
        $resolver = $this->getRememberDeviceResolver(true);
        $doctrine = $this->getDoctrine();

        $response = (new AuthenticationSuccessHandler(new LexikAuthenticationSuccessHandler($manager, $dispatcher), $encoder, 'strict', $resolver, $doctrine))
            ->onAuthenticationSuccess($request, $token);

        $cookies = $response->headers->getCookies();
        $this->assertCount(2, $cookies);
        $this->assertSame('BEARER', $cookies[0]->getName());
        $this->assertSame('REMEMBER_DEVICE', $cookies[1]->getName());
        $this->assertSame(['user' => 'example@example.org', 'exp' => 1627902433], $encoder->decode($cookies[1]->getValue()));

        $encoder = $this->getEncoder('newuser@example.org');
        $response = (new AuthenticationSuccessHandler(new LexikAuthenticationSuccessHandler($manager, $dispatcher), $encoder, 'strict', $resolver, $doctrine))
            ->onAuthenticationSuccess($request, $token);

        $cookies = $response->headers->getCookies();
        $this->assertCount(2, $cookies);
        $this->assertSame('BEARER', $cookies[0]->getName());
        $this->assertSame('REMEMBER_DEVICE', $cookies[1]->getName());
        $this->assertSame(['user' => 'newuser@example.org', 'exp' => 1627902433], $encoder->decode($cookies[1]->getValue()));

    }

    private function getEncoder($user = 'example@example.org'): JWTEncoderInterface
    {
        $encoder = $this->createMock(JWTEncoderInterface::class);

        $encoder
            ->expects($this->any())
            ->method('decode')
            ->willReturn(['user' => $user, 'exp' => 1627902433]);

        $encoder
            ->expects($this->any())
            ->method('encode')
            ->willReturn('encoded_value');

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

        $request->request = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $request->cookies = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Cookie')
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $request->request
            ->expects($this->any())
            ->method('get')
            ->with('username')
            ->will($this->returnValue('name'));

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
            ->expects($this->any())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(AuthenticationSuccessEvent::class),
                $this->equalTo(Events::AUTHENTICATION_SUCCESS)
            );

        return $dispatcher;
    }

    private function getRememberDeviceResolver($status)
    {
        $rememberDeviceResolver = $this->createMock(RememberDeviceResolver::class);

        $rememberDeviceResolver
            ->expects($this->any())
            ->method('getRememberDeviceStatus')
            ->willReturn($status);

        return $rememberDeviceResolver;
    }


    private function getDoctrine()
    {
        return $this->createMock(ManagerRegistry::class);
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
