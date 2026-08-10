<?php

namespace App\Modules\Api\User\Token\Destroy;

use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\SharedSchema;
use App\Modules\Api\User\Token\TokenParameter;
use App\Routes\ApiRoute;
use ReflectionException;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class UserTokenDestroySchema implements DescribesOperation
{
    /**
     * @return array{paths?: array<string, PathItem>, components?: Components}
     *
     * @throws ReflectionException
     */
    public static function schema(): array
    {
        return [
            'components' => SharedSchema::components,
            'paths' => [
                ApiRoute::user_token->value => [
                    'delete' => [
                        'operationId' => 'revokeUserToken',
                        'summary' => 'Revoke one personal access token of the authenticated user.',
                        'tags' => ['Tokens'],
                        'security' => [[SharedSchema::bearer => []]],
                        'parameters' => [TokenParameter::schema()],
                        'responses' => [
                            // 200 rather than the REST-conventional 204: every
                            // response this API serves carries the envelope,
                            // and a 204 has no body to carry it in.
                            '200' => [
                                'description' => 'The token was revoked.',
                                'content' => [
                                    'application/json' => ['schema' => UserTokenDestroyResponse::schema()],
                                ],
                            ],
                            '401' => [
                                'description' => SharedSchema::middleware_error_description,
                                'content' => [
                                    'application/json' => [
                                        'schema' => ['$ref' => SharedSchema::middleware_error],
                                    ],
                                ],
                            ],
                            '404' => [
                                'description' => 'The authenticated user has no token with that id.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => ['$ref' => SharedSchema::api_error],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
