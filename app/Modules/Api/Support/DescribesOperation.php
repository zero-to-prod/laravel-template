<?php

namespace App\Modules\Api\Support;

use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * A class that builds an OpenAPI document fragment for one route.
 *
 * The return shape is imported from ApiSchema so RequestSchema can forward it
 * to the parent constructor without a cast.
 *
 * @phpstan-import-type PathItem from ApiSchema
 * @phpstan-import-type Components from ApiSchema
 */
interface DescribesOperation
{
    /** @return array{paths?: array<string, PathItem>, components?: Components} */
    public static function schema(): array;
}
