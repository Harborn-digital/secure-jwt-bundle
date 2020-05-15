<?php

declare(strict_types=1);

/*
 * This file is part of the Connect Holland Secure JWT package.
 * (c) Connect Holland.
 */

namespace ConnectHolland\SecureJWTBundle\Swagger;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LoginDecorator implements NormalizerInterface
{
    private NormalizerInterface $decorated;

    /**
     * LoginDecorator constructor.
     */
    public function __construct(NormalizerInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        $docs = $this->decorated->normalize($object, $format, $context);

        if (false === array_key_exists('paths', $docs)) {
            $docs['paths'] = new \ArrayObject();
        }
        if (false === array_key_exists('/api/logouts', $docs['paths'])) {
            $docs['paths']['/api/logouts']         = new \ArrayObject();
            $docs['paths']['/api/logouts']['post'] = new \ArrayObject();
        }
        $docs['paths']['/api/logouts']['post']['tags']        = ['Authentication'];
        $docs['paths']['/api/logouts']['post']['summary']     = 'Invalidate JWT token';
        $docs['paths']['/api/logouts']['post']['description'] = 'Log the current user out by invalidating their JWT token. The logout field in the message body may contain any value and is not used.';
        $docs['paths']['/api/login_check']                    = $this->getLoginDocumentation();

        return $docs;
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    private function getLoginDocumentation(): array
    {
        return [
            'post' => [
                'tags'        => ['Authentication'],
                'description' => 'Two factor login, after a valid login the JWT token will be set as a secure cookie',
                'summary'     => 'Two Factor Login',
                'operationId' => 'login',
                'responses'   => [
                    '200' => [
                        'description' => 'Login complete',
                        'content'     => [
                            'application/json' => [
                                'schema' => [
                                    'type'       => 'object',
                                    'properties' => [
                                        'result' => [
                                            'type' => 'string',
                                        ],
                                    ],
                                    'example' => [
                                        'result' => 'ok',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => [
                        'description' => 'Login failed or incomplete',
                        'content'     => [
                            'application/json' => [
                                'schema' => [
                                    'type'       => 'object',
                                    'properties' => [
                                        'code' => [
                                            'type' => 'integer',
                                        ],
                                        'result' => [
                                            'type' => 'string',
                                        ],
                                        'message' => [
                                            'type' => 'string',
                                        ],
                                        'status' => [
                                            'type' => 'string',
                                        ],
                                        'qr' => [
                                            'type' => 'string',
                                        ],
                                    ],
                                    'example' => [
                                        'result'  => 'ok',
                                        'message' => 'use provided QR code to set up two factor authentication',
                                        'qr'      => 'QR code (data URL)',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type'     => 'object',
                                'required' => [
                                    'username',
                                    'password',
                                ],
                                'optional' => [
                                    'challenge',
                                ],
                                'properties' => [
                                    'username' => [
                                        'type' => 'string',
                                    ],
                                    'password' => [
                                        'type' => 'string',
                                    ],
                                    'challenge' => [
                                        'type' => 'string',
                                    ],
                                ],
                                'example' => [
                                    'username'  => 'example@example.org',
                                    'password'  => 'secret',
                                    'challenge' => '123456',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
