<?php

namespace App\Modules\Api\Admin\LogInvestigation;

use App\Modules\Api\Support\DescribesOperation;
use App\Modules\Api\Support\SharedSchema;
use App\Routes\Admin;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
readonly class AdminLogInvestigationSchema implements DescribesOperation
{
    /** @return array{paths?: array<string, PathItem>, components?: Components} */
    public static function schema(): array
    {
        return [
            'components' => SharedSchema::components,
            'paths' => [
                Admin::api_logs_investigate->value => [
                    'get' => [
                        'operationId' => 'investigateLogs',
                        'summary' => 'Investigate application logs and group repeated failures into compact findings.',
                        'tags' => ['Log Investigation'],
                        'parameters' => AdminLogInvestigationParameters::schema(),
                        'responses' => [
                            '200' => [
                                'description' => 'Compact evidence grouped by repeated failure.',
                                'content' => [
                                    'application/json' => ['schema' => AdminLogInvestigationResponse::schema()],
                                ],
                            ],
                            '401' => [
                                'description' => SharedSchema::middleware_error_description,
                                'content' => [
                                    'application/json' => ['schema' => ['$ref' => SharedSchema::middleware_error]],
                                ],
                            ],
                            '422' => [
                                'description' => 'The investigation filters failed validation.',
                                'content' => [
                                    'application/json' => ['schema' => ['$ref' => SharedSchema::api_validation_error]],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
