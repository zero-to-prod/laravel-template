<?php

namespace App\Modules\Api\Admin\LogInvestigation;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use ZeroToProd\LaravelOpenapi\ApiSchema;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

/**
 * @phpstan-import-type OpenApiSchema from ApiSchema
 * @phpstan-import-type Parameter from ApiSchema
 */
readonly class AdminLogInvestigationParameters
{
    /** @return list<Parameter> */
    public static function schema(): array
    {
        return [
            self::parameter('query', 'Text or regular expression describing the symptom.', [Property::type => Property::string]),
            self::parameter('host', 'The log host. Only local is currently supported.', [Property::type => Property::string, Property::enum => ['local']]),
            self::parameter('file', 'An encoded log file identifier. Omit to search every local log file.', [Property::type => Property::string]),
            self::parameter('levels', 'Only include these severity levels, such as ERROR or WARNING.', [Property::type => Schema::array, Schema::items => [Property::type => Property::string]]),
            self::parameter('environments', 'Only include these application environments, such as production.', [Property::type => Schema::array, Schema::items => [Property::type => Property::string]]),
            self::parameter('since', 'Only include entries at or after this ISO-8601 datetime.', [Property::type => Property::string, Property::format => 'date-time']),
            self::parameter('until', 'Only include entries at or before this ISO-8601 datetime.', [Property::type => Property::string, Property::format => 'date-time']),
            self::parameter('direction', 'Return oldest or newest evidence first.', [Property::type => Property::string, Property::enum => ['asc', 'desc'], Property::default => 'desc']),
            self::parameter('limit', 'Maximum grouped findings to return.', [Property::type => Property::integer, Property::minimum => 1, Property::maximum => 25, Property::default => 10]),
            self::parameter('cursor', 'Cursor from a previous response.', [Property::type => Property::string, Property::pattern => '^[1-9][0-9]*$']),
            self::parameter('include_context', 'Include representative full text and exception context inline.', [Property::type => Property::boolean, Property::default => false]),
        ];
    }

    public static function validator(Request $Request): Validator
    {
        $input = $Request->query->all();

        foreach (['levels', 'environments'] as $key) {
            if (isset($input[$key]) && is_string($input[$key])) {
                $input[$key] = [$input[$key]];
            }
        }

        if (isset($input['include_context'])) {
            $input['include_context'] = $Request->boolean('include_context');
        }

        return ValidatorFacade::make($input, [
            'query' => ['nullable', 'string', 'max:500'],
            'host' => ['nullable', 'in:local'],
            'file' => ['nullable', 'string', 'max:500'],
            'levels' => ['nullable', 'array'],
            'levels.*' => ['string', 'max:50'],
            'environments' => ['nullable', 'array'],
            'environments.*' => ['string', 'max:100'],
            'since' => ['nullable', 'date'],
            'until' => ['nullable', 'date', 'after_or_equal:since'],
            'direction' => ['nullable', 'in:asc,desc'],
            'limit' => ['nullable', 'integer', 'between:1,25'],
            'cursor' => ['nullable', 'string', 'regex:/^[1-9][0-9]*$/'],
            'include_context' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * @param  OpenApiSchema  $schema
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
