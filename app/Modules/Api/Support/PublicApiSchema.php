<?php

namespace App\Modules\Api\Support;

use Attribute;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/** Declares an operation in the public OpenAPI document. */
#[Attribute(Attribute::TARGET_METHOD)]
class PublicApiSchema extends ApiSchema {}
