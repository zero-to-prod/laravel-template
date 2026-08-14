<?php

use App\Helpers\Gravatar;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    Http::swap(new Factory);
});

test('url normalizes and hashes the email', function (): void {
    expect(Gravatar::url(' MyEmailAddress@example.com '))
        ->toBe('https://www.gravatar.com/avatar/84059b07d4be67b806386c0aad8070a23f18836bbaae342275dc0a83414c32ee?s=80&d=404&r=g');
});

test('image returns an inline image', function (): void {
    Http::fake([
        '*' => Http::response('image contents', 200, ['Content-Type' => 'image/png']),
    ]);

    expect(Gravatar::image('person@example.com'))
        ->toBe('data:image/png;base64,'.base64_encode('image contents'));
});

test('image returns null when gravatar cannot be reached', function (): void {
    Http::fake([
        '*' => Http::failedConnection(),
    ]);

    expect(Gravatar::image('person@example.com'))->toBeNull();
});

test('image returns null for an unsuccessful response', function (): void {
    Http::fake([
        '*' => Http::response(status: 404, headers: ['Content-Type' => 'image/png']),
    ]);

    expect(Gravatar::image('person@example.com'))->toBeNull();
});

test('image returns null when the response is not an image', function (): void {
    Http::fake([
        '*' => Http::response('not an image', headers: ['Content-Type' => 'text/plain']),
    ]);

    expect(Gravatar::image('person@example.com'))->toBeNull();
});
