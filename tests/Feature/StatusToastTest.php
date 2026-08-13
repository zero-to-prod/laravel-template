<?php

use App\View\DataModels\StatusToast;

test('defaults are resolved from the props array', function (): void {
    $StatusToast = StatusToast::from();

    expect($StatusToast->sessionKey)->toBe('status')
        ->and($StatusToast->alert)->toBe('alert-success')
        ->and($StatusToast->message)->toBeNull();
});

test('the message falls back to the flashed session value', function (): void {
    session()->put('status', 'Verification link sent!');
    session()->put('warning', 'Careful.');

    expect(StatusToast::from([])->message)->toBe('Verification link sent!')
        ->and(StatusToast::from([StatusToast::sessionKey => 'warning'])->message)->toBe('Careful.')
        ->and(StatusToast::from([StatusToast::sessionKey => 'missing'])->message)->toBeNull();
});

test('a passed message wins over the session', function (): void {
    session()->put('status', 'Flashed.');

    $StatusToast = StatusToast::from([
        StatusToast::message => 'Passed.',
        StatusToast::alert => 'alert-error',
    ]);

    expect($StatusToast->message)->toBe('Passed.')
        ->and($StatusToast->alert)->toBe('alert-error');
});
