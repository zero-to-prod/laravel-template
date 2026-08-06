<?php

use App\Modules\Api\Api;
use App\Routes\Web;

test('api response resolves the api responder', function (): void {
    expect(api_response())->toBeInstanceOf(Api::class);
});

test('render url substitutes route parameters', function (): void {
    expect(render_url(Web::verificationVerify->value, ['id' => 1, 'hash' => 'abc']))
        ->toBe('/email/verify/1/abc')
        ->and(render_url(Web::login->value, []))->toBe(Web::login->value);
});
