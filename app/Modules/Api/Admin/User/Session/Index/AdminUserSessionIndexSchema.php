<?php

namespace App\Modules\Api\Admin\User\Session\Index;

use App\Modules\Api\Admin\User\UserParameter;
use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\PaginationParameters;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\Admin;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class AdminUserSessionIndexSchema implements DescribesOperation
{
    /** @return array{paths?: array<string, PathItem>, components?: Components} */
    public static function schema(): array
    {
        return [
            'components' => SharedSchema::components,
            'paths' => [
                Admin::api_user_sessions->value => [
                    'get' => [
                        'operationId' => 'listAdminUserSessions',
                        'summary' => 'List a user\'s sessions.',
                        'tags' => ['Admin Sessions'],
                        'parameters' => [UserParameter::schema(), ...PaginationParameters::schema()],
                        'responses' => [
                            '200' => [
                                'description' => 'The user\'s paginated sessions.',
                                'content' => [
                                    'application/json' => ['schema' => AdminUserSessionIndexResponse::schema()],
                                ],
                            ],
                            '401' => [
                                'description' => SharedSchema::middleware_error_description,
                                'content' => [
                                    'application/json' => ['schema' => ['$ref' => SharedSchema::middleware_error]],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
