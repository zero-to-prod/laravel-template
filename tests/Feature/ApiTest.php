<?php

use App\Modules\Api\Support\ErrorCode;
use Tests\Fixtures\RequestStub;

test('not found responds with 404 and echoes the supplied data', function (): void {
    $JsonResponse = api_response()->notFound(ErrorCode::unauthorized, ['id' => 1]);

    expect($JsonResponse->getStatusCode())->toBe(404)
        ->and($JsonResponse->getData(true))->toBe([
            'success' => false,
            'message' => ErrorCode::unauthorized->value,
            'errors' => [ErrorCode::unauthorized->value],
            'data' => ['id' => 1],
            'type' => 'error',
        ]);
});

test('conflict responds with 409', function (): void {
    $JsonResponse = api_response()->conflict(ErrorCode::token_not_found);

    expect($JsonResponse->getStatusCode())->toBe(409)
        ->and($JsonResponse->getData(true))->toBe([
            'success' => false,
            'message' => ErrorCode::token_not_found->value,
            'errors' => [ErrorCode::token_not_found->value],
            'type' => 'error',
        ]);
});

test('unsupported media type responds with 415', function (): void {
    $JsonResponse = api_response()->unsupportedMediaType(ErrorCode::unsupported_media_type);

    expect($JsonResponse->getStatusCode())->toBe(415)
        ->and($JsonResponse->getData(true))->toBe([
            'success' => false,
            'message' => ErrorCode::unsupported_media_type->value,
            'errors' => [ErrorCode::unsupported_media_type->value],
            'type' => 'error',
        ]);
});

test('created responds with 201', function (): void {
    $JsonResponse = api_response()->created(['id' => 1]);

    expect($JsonResponse->getStatusCode())->toBe(201)
        ->and($JsonResponse->getData(true))->toBe([
            'success' => true,
            'data' => ['id' => 1],
        ]);
});

test('an array payload has no type', function (): void {
    expect(api_response()->ok(['id' => 1])->getData(true))->toBe([
        'success' => true,
        'data' => ['id' => 1],
    ]);
});

test('fields filter flat keys, lists of records and nested objects', function (): void {
    $data = [
        'id' => 1,
        'secret' => 'hidden',
        'items' => [['name' => 'one', 'secret' => 'hidden'], ['name' => 'two', 'secret' => 'hidden']],
        'profile' => (object) ['city' => 'Fort Wayne', 'secret' => 'hidden'],
    ];

    $JsonResponse = api_response()->created($data, [
        'id',
        'absent',
        'items' => ['name'],
        'profile' => ['city'],
        'absent_group' => ['city'],
    ]);

    expect($JsonResponse->getData(true))->toBe([
        'success' => true,
        'data' => [
            'id' => 1,
            'items' => [['name' => 'one'], ['name' => 'two']],
            'profile' => ['city' => 'Fort Wayne'],
        ],
    ]);
});

test('fields filter a payload object by calling to array on it', function (): void {
    $JsonResponse = api_response()->ok(
        RequestStub::make(),
        [RequestStub::website]
    );

    expect($JsonResponse->getData(true))->toBe([
        'success' => true,
        'message' => 'RequestStub',
        'data' => ['website' => 'https://example.com'],
        'type' => 'RequestStub',
    ]);
});
