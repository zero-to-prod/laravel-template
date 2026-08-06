<?php

use App\Helpers\Rule;
use App\Modules\Login\LoginForm;
use Tests\Fixtures\FieldStub;

test('rules are collected from field metadata and skip properties without rules', function (): void {
    expect(FieldStub::make()->rules())->toBe([
        FieldStub::website => [Rule::required->value, Rule::url->value],
        FieldStub::secret => [Rule::nullable->value, Rule::string->value],
    ]);
});

test('a pipe delimited rule string is normalised to a list', function (): void {
    expect(LoginForm::from([LoginForm::email => 'john@example.com', LoginForm::password => 'password'])->rules())
        ->toBe([
            LoginForm::email => [Rule::required->value, Rule::string->value, Rule::email->value, Rule::max(255)],
            LoginForm::password => [Rule::required->value, Rule::string->value, Rule::max(255)],
        ]);
});

test('messages are keyed by property and rule', function (): void {
    expect(FieldStub::make()->messages())->toBe([
        FieldStub::website.'.'.Rule::required->value => 'A website is required.',
    ]);
});

test('attributes are collected for fields that declare them', function (): void {
    expect(FieldStub::make()->attributes())->toBe([FieldStub::website => 'website address']);
});

test('validator returns the payload, rules, messages and attributes', function (): void {
    $FieldStub = FieldStub::make();

    expect($FieldStub->validator())->toBe([
        $FieldStub->toArray(),
        $FieldStub->rules(),
        $FieldStub->messages(),
        $FieldStub->attributes(),
    ]);
});

test('is required reads array and pipe delimited rules and defaults to false', function (): void {
    expect(FieldStub::isRequired(FieldStub::website))->toBeTrue()
        ->and(FieldStub::isRequired(FieldStub::secret))->toBeFalse()
        ->and(FieldStub::isRequired(FieldStub::blank))->toBeFalse()
        ->and(FieldStub::isRequired(FieldStub::untagged))->toBeFalse()
        ->and(FieldStub::isRequired(FieldStub::undescribed))->toBeFalse();
});

test('is sensitive reflects the field flag', function (): void {
    expect(FieldStub::isSensitive(FieldStub::secret))->toBeTrue()
        ->and(FieldStub::isSensitive(FieldStub::website))->toBeFalse()
        ->and(FieldStub::isSensitive(FieldStub::untagged))->toBeFalse();
});

test('placeholder is read from field metadata', function (): void {
    expect(FieldStub::placeholder(FieldStub::website))->toBe('https://example.com')
        ->and(FieldStub::placeholder(FieldStub::secret))->toBeNull()
        ->and(FieldStub::placeholder(FieldStub::untagged))->toBeNull();
});

test('legend is read from field metadata', function (): void {
    expect(FieldStub::legend(FieldStub::website))->toBe('Website')
        ->and(FieldStub::legend(FieldStub::blank))->toBeNull()
        ->and(FieldStub::legend(FieldStub::untagged))->toBeNull();
});

test('description is read from field metadata and an empty description is null', function (): void {
    expect(FieldStub::description(FieldStub::website))->toBe('Homepage')
        ->and(FieldStub::description(FieldStub::blank))->toBeNull()
        ->and(FieldStub::description(FieldStub::untagged))->toBeNull();
});

test('type is derived from sensitivity then rules', function (): void {
    expect(FieldStub::type(FieldStub::secret))->toBe('password')
        ->and(FieldStub::type(FieldStub::website))->toBe('url')
        ->and(FieldStub::type(FieldStub::blank))->toBe('text')
        ->and(FieldStub::type(FieldStub::untagged))->toBe('text');
});
