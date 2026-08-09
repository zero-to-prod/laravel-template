<?php

namespace App\Modules\Api\Support;

use Attribute;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/**
 * Builds the route's OpenAPI fragment at document-build time.
 *
 * ApiSchema takes a literal array, which attribute arguments must be, so a
 * fragment assembled from reflection cannot be passed to it directly.
 * SchemaGenerator resolves ApiSchema subclasses (IS_INSTANCEOF) and calls
 * newInstance(), which runs this constructor.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class RequestSchema extends ApiSchema
{
    /** @param  class-string<DescribesOperation>  $schema */
    public function __construct(string $schema)
    {
        parent::__construct($schema::schema());
    }
}
