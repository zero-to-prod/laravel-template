<?php

namespace Tests\Fixtures\OpenApi;

use App\Modules\Api\Support\AdminApiSchema;
use Illuminate\Http\JsonResponse;

readonly class AdminSchemaController
{
    #[AdminApiSchema([
        'paths' => [
            '/admin/schema-test' => [
                'get' => [
                    'operationId' => 'adminSchemaTest',
                    'responses' => [200 => ['description' => 'The admin-only response.']],
                ],
            ],
        ],
    ])]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse;
    }
}
