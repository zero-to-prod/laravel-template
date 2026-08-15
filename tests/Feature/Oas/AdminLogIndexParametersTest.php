<?php

use App\Modules\Api\Log\Index\AdminLogIndexParameters;
use Illuminate\Http\Request;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

test('every supported log search parameter is documented', function (): void {
    $parameters = collect(AdminLogIndexParameters::schema())->keyBy('name');

    expect($parameters->keys()->all())->toBe([
        'file',
        'query',
        'direction',
        'page',
        'per_page',
        'exclude_levels',
        'exclude_file_types',
        'include_context',
    ])->and(AdminLogIndexParameters::schema())->toContain(
        [
            'name' => 'direction',
            'in' => 'query',
            'required' => false,
            'description' => 'The order in which matching entries are returned.',
            'schema' => [
                Property::type => Property::string,
                Property::enum => ['asc', 'desc'],
                Property::default => 'desc',
            ],
        ],
        [
            'name' => 'exclude_levels',
            'in' => 'query',
            'required' => false,
            'description' => 'Log levels to omit.',
            'schema' => [
                Property::type => Schema::array,
                Schema::items => [Property::type => Property::string],
            ],
        ],
        [
            'name' => 'exclude_file_types',
            'in' => 'query',
            'required' => false,
            'description' => 'Log file types to omit from a cross-file search.',
            'schema' => [
                Property::type => Schema::array,
                Schema::items => [Property::type => Property::string],
            ],
        ],
        [
            'name' => 'include_context',
            'in' => 'query',
            'required' => false,
            'description' => 'Include full_text and context.exception in every log entry.',
            'schema' => [
                Property::type => Property::boolean,
                Property::default => false,
            ],
        ],
    );
});

test('context is excluded by default and included only when requested', function (): void {
    expect(AdminLogIndexParameters::includesContext(new Request))->toBeFalse()
        ->and(AdminLogIndexParameters::includesContext(new Request(['include_context' => true])))->toBeTrue();
});
