<?php

namespace App\Modules\Api\Support;

use Attribute;
use ZeroToProd\LaravelOpenapi\ApiSchema;

/** Declares an operation in the independently protected admin OpenAPI document. */
#[Attribute(Attribute::TARGET_METHOD)]
class AdminApiSchema extends ApiSchema {}
