<?php

namespace App\Modules\Api\Support;

use Illuminate\Http\Request;
use ZeroToProd\LaravelOpenapi\ApiSchema;
use ZeroToProd\SchemaValidator\Property;

/**
 * The query parameters every index operation reads, shared so the whole set
 * pages the same way.
 *
 * Nothing enforces a query parameter at runtime: the document describes them
 * and league checks them in tests, but no middleware stands between the
 * request and the controller. So `perPage()` clamps what arrived rather than
 * trusting it, and the declared `maximum` is a description of that clamp
 * rather than the thing doing it.
 *
 * @phpstan-import-type Parameter from ApiSchema
 */
readonly class PaginationParameters
{
    public const string page = 'page';
    public const string per_page = 'per_page';
    public const int default_per_page = 15;
    public const int max_per_page = 100;

    /**
     * @return list<Parameter>
     */
    public static function schema(): array
    {
        return [
            [
                'name' => self::page,
                'in' => 'query',
                'required' => false,
                'description' => 'The page to return, counting from 1. A page past the last one is empty rather than a 404.',
                'schema' => [
                    Property::type => Property::integer,
                    Property::minimum => 1,
                    Property::default => 1,
                ],
            ],
            [
                'name' => self::per_page,
                'in' => 'query',
                'required' => false,
                'description' => sprintf('How many entries a page carries. Anything above %d is served as %d.', self::max_per_page, self::max_per_page),
                'schema' => [
                    Property::type => Property::integer,
                    Property::minimum => 1,
                    Property::maximum => self::max_per_page,
                    Property::default => self::default_per_page,
                ],
            ],
        ];
    }

    public static function perPage(Request $Request): int
    {
        return max(1, min(self::max_per_page, $Request->integer(self::per_page, self::default_per_page)));
    }
}
