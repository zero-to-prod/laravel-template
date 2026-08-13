<?php

use App\Modules\Login\LoginForm;
use Illuminate\Support\Facades\Event;
use Tests\Fixtures\RequestStub;

test('a data model collects, serialises and converts to an array', function (): void {
    $LoginForm = LoginForm::from([
        LoginForm::email => 'john@example.com',
        LoginForm::password => 'password',
    ]);

    expect($LoginForm->collect()->all())->toBe([
        LoginForm::email => 'john@example.com',
        LoginForm::password => 'password',
        LoginForm::remember_token => false,
    ])
        ->and($LoginForm->toJson())->toBe(json_encode($LoginForm->collect()->all()))
        ->and($LoginForm->toArray())->toBe($LoginForm->collect()->all());
});

test('dispatch fires the data model as an event', function (): void {
    Event::fake();

    LoginForm::from([LoginForm::email => 'john@example.com', LoginForm::password => 'password'])->dispatch();

    Event::assertDispatched(LoginForm::class);
});

test('sanitize squishes whitespace', function (): void {
    expect(RequestStub::sanitize("  a   b \n"))->toBe('a b');
});

test('sanitize email squishes and lowercases', function (): void {
    expect(RequestStub::sanitizeEmail('  JOHN@Example.COM '))->toBe('john@example.com');
});
