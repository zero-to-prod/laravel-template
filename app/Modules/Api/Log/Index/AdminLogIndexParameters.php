<?php

namespace App\Modules\Api\Log\Index;

use Illuminate\Http\Request;
use ZeroToProd\LaravelOpenapi\ApiSchema;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

/** @phpstan-import-type Parameter from ApiSchema */
readonly class AdminLogIndexParameters
{
    public const string include_context = 'include_context';

    /** @return list<Parameter> */
    public static function schema(): array
    {
        return [
            self::parameter('file', 'The encoded log file identifier.', [Property::type => Property::string]),
            self::parameter('query', 'Text or regular expression to search for.', [Property::type => Property::string]),
            self::parameter('direction', 'The order in which matching entries are returned.', [
                Property::type => Property::string,
                Property::enum => ['asc', 'desc'],
                Property::default => 'desc',
            ]),
            self::parameter('page', 'The page to return, counting from 1.', [
                Property::type => Property::integer,
                Property::minimum => 1,
                Property::default => 1,
            ]),
            self::parameter('per_page', 'How many log entries a page carries.', [
                Property::type => Property::integer,
                Property::minimum => 1,
                Property::default => 25,
            ]),
            self::parameter('exclude_levels', 'Log levels to omit.', [
                Property::type => Schema::array,
                Schema::items => [Property::type => Property::string],
            ]),
            self::parameter('exclude_file_types', 'Log file types to omit from a cross-file search.', [
                Property::type => Schema::array,
                Schema::items => [Property::type => Property::string],
            ]),
            self::parameter(self::include_context, 'Include full_text and context.exception in every log entry.', [
                Property::type => Property::boolean,
                Property::default => false,
            ]),
        ];
    }

    public static function includesContext(Request $Request): bool
    {
        return $Request->boolean(self::include_context);
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return Parameter
     */
    private static function parameter(string $name, string $description, array $schema): array
    {
        return [
            'name' => $name,
            'in' => 'query',
            'required' => false,
            'description' => $description,
            'schema' => $schema,
        ];
    }
}
