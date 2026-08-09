<?php

use App\Helpers\Rule;
use App\Modules\Login\LoginRequest;
use Tests\Fixtures\RequestStub;

test('rules are collected from request metadata and skip properties without rules', function (): void {
    expect(RequestStub::make()->rules())->toBe([
        RequestStub::website => [Rule::required->value, Rule::url->value],
        RequestStub::secret => [Rule::nullable->value, Rule::string->value],
        RequestStub::callable => [Rule::nullable->value, Rule::max(10)],
    ]);
});

test('a pipe delimited rule string is normalised to a list', function (): void {
    expect(LoginRequest::from([LoginRequest::email => 'john@example.com', LoginRequest::password => 'password'])->rules())
        ->toBe([
            LoginRequest::email => [Rule::required->value, Rule::string->value, Rule::email->value, Rule::max(255)],
            LoginRequest::password => [Rule::required->value, Rule::string->value, Rule::max(255)],
        ]);
});

test('messages are keyed by property and rule', function (): void {
    expect(RequestStub::make()->messages())->toBe([
        RequestStub::website.'.'.Rule::required->value => 'A website is required.',
    ]);
});

test('attributes are collected for properties that declare them', function (): void {
    expect(RequestStub::make()->attributes())->toBe([RequestStub::website => 'website address']);
});

test('validator returns the payload, rules, messages and attributes', function (): void {
    $RequestStub = RequestStub::make();

    expect($RequestStub->validator())->toBe([
        $RequestStub->toArray(),
        $RequestStub->rules(),
        $RequestStub->messages(),
        $RequestStub->attributes(),
    ]);
});
