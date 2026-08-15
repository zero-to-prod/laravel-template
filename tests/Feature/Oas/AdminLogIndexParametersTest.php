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
    ])->and($parameters['direction']['schema'][Property::enum])->toBe(['asc', 'desc'])
        ->and($parameters['exclude_levels']['schema'][Property::type])->toBe(Schema::array)
        ->and($parameters['exclude_file_types']['schema'][Property::type])->toBe(Schema::array)
        ->and($parameters['include_context']['schema'])->toBe([
            Property::type => Property::boolean,
            Property::default => false,
        ]);
});

test('context is excluded by default and included only when requested', function (): void {
    expect(AdminLogIndexParameters::includesContext(new Request))->toBeFalse()
        ->and(AdminLogIndexParameters::includesContext(new Request(['include_context' => true])))->toBeTrue();
});
