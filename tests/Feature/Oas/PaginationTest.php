<?php

use App\Modules\Api\Support\PaginationParameters;
use App\Modules\Api\Support\PaginationResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use ZeroToProd\SchemaValidator\Property;
use ZeroToProd\SchemaValidator\Schema;

test('the object reports the page it carries and how many there are in total', function (): void {
    $PaginationResponse = PaginationResponse::of(new LengthAwarePaginator([1, 2], 5, 2, 2));

    expect($PaginationResponse->page)->toBe(2)
        ->and($PaginationResponse->per_page)->toBe(2)
        ->and($PaginationResponse->total)->toBe(5)
        ->and($PaginationResponse->last_page)->toBe(3);
});

test('every counter is published as a required integer', function (): void {
    expect(PaginationResponse::data())->toBe([
        Schema::type => Schema::object,
        Schema::required => [
            PaginationResponse::page,
            PaginationResponse::per_page,
            PaginationResponse::total,
            PaginationResponse::last_page,
        ],
        Schema::properties => [
            PaginationResponse::page => [
                Property::type => Property::integer,
                Property::description => 'The page this body carries, counting from 1.',
            ],
            PaginationResponse::per_page => [
                Property::type => Property::integer,
                Property::description => 'How many entries a full page carries.',
            ],
            PaginationResponse::total => [
                Property::type => Property::integer,
                Property::description => 'How many entries there are across every page.',
            ],
            PaginationResponse::last_page => [
                Property::type => Property::integer,
                Property::description => 'The highest page that carries anything. 1 when there is nothing at all.',
            ],
        ],
    ]);
});

test('the query parameters are optional, and the ceiling is the one the clamp applies', function (): void {
    expect(PaginationParameters::schema())->toBe([
        [
            'name' => PaginationParameters::page,
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
            'name' => PaginationParameters::per_page,
            'in' => 'query',
            'required' => false,
            'description' => 'How many entries a page carries. Anything above 100 is served as 100.',
            'schema' => [
                Property::type => Property::integer,
                Property::minimum => 1,
                Property::maximum => PaginationParameters::max_per_page,
                Property::default => PaginationParameters::default_per_page,
            ],
        ],
    ]);
});

test('a page size is clamped rather than trusted, and an absent one is the default', function (): void {
    expect(PaginationParameters::perPage(new Request))->toBe(PaginationParameters::default_per_page)
        ->and(PaginationParameters::perPage(new Request([PaginationParameters::per_page => 5])))->toBe(5)
        ->and(PaginationParameters::perPage(new Request([PaginationParameters::per_page => 1000])))
        ->toBe(PaginationParameters::max_per_page)
        ->and(PaginationParameters::perPage(new Request([PaginationParameters::per_page => 0])))->toBe(1);
});
